<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>

<div class="container">
    <div class="row content">

        <div id='loginForm'>
            <div class="col-sm-4 col-sm-offset-4">
                <p>Admin Login</p>

                <div class="form-group">
                    <input type="text" id="userName" placeholder="Your Name" class="form-control">
                </div>

                <div class="form-group">
                    <input type="email" id="userEmail" placeholder="Email address" class="form-control">
                </div>

                <button id="signInBtn" class="btn btn-primary btn-block">Sign in</button>
            </div>
        </div><!--/#loginForm -->


        <div id='chatForm' style="display:none;">

            <div class="col-sm-4 col-sm-offset-2">
                <h3>List of customers</h3>
                <div id='activeUsers'></div>
            </div>

            <div class="col-sm-4">
                <button type="button" id="exitChatBtn" class="close" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>


                <h3>Admin chat form</h3>
                <div class="panel panel-warning">
                    <div class="panel-heading" id="currentChatClient"></div>
                    <div class="panel-body" id="postedMsgs" style="min-height: 220px;"></div>
                </div>

                <div class="form-group">
                    <input type="text" 
                            id="textMsg" 
                            value="" 
                            placeholder="Enter text here..." 
                            class="form-control">

                    <input type="hidden" id="chatRoomToken" value="">        
                </div>

                <button id="sendMsgBtn" class="btn btn-primary btn-block">Send</button>

            </div>
        </div><!--/#chatForm -->
  </div>
</div>

<script>
    const AdminChatClient = new(function() {
        const _root = this;
        let _cfg = {
            token:              '',
            connection:         null,
            postedMessagesEl:   $('#postedMsgs'),
            activeUsers:        $('#activeUsers'),
            loginFormEl:        $('#loginForm'),
            userNameInput:      $('#userName'),
            userEmailInput:     $('#userEmail'),
            signInButton:       $('#signInBtn'),
            chatFormEl:         $('#chatForm'),
            currentChatClient:  $('#currentChatClient'),
            textMsgInput:       $('#textMsg'),
            chatRoomTokenInput: $('#chatRoomToken'),
            sendMsgButton:      $('#sendMsgBtn'),
            chatErrorsEl:       $('#chatErrors'),
            exitChatButton:     $('#exitChatBtn'),
        }

        _root.init = function(options) {
            _cfg = $.extend(_cfg, options);

            if(_isTokenExist()){
                _initWebSocket();
                _initChatForm();
            }
            _bindUIActions();
        }

        const _bindUIActions = function(){
            _cfg.signInButton.on('click', () =>{
                _signIn();
            })

            _cfg.sendMsgButton.on('click', () =>{
                _sendMsg();
            })

            _cfg.exitChatButton.on('click', () => {
                _cfg.conn.close();
            })

            _cfg.activeUsers.on('click', (e) => {
                const chatRoomToken = $(e.target).attr('data-token');
                const userName = $(e.target).text()

                _cfg.chatRoomTokenInput.val(chatRoomToken);
                _cfg.currentChatClient.html(`Chat with <b>${userName}</b>`);
                _getStorredMessagesForChatRoom(chatRoomToken);
            })
        }

        const _isTokenExist = function(){
            const token = sessionStorage.getItem('token');
            if(token){
                _cfg.token = token;
                return true;
            }
        }

        const _setToken = function(token){
            _cfg.token = token || '';
            sessionStorage.setItem('token', _cfg.token);
        }

        const _initWebSocket = function(){
            _cfg.conn = new WebSocket('ws://localhost:8080?token=' + _cfg.token);
            _cfg.conn.onopen = e => _onOpenHandler();
            _cfg.conn.onclose = e => _onCloseHandler();
            _cfg.conn.onmessage = e => _onMessageHandler(e.data);
        }

        const _onOpenHandler = function(){
            console.log("Connection established!");
        }

        const _onCloseHandler = function(){
            console.log("Connection closed!");
            _signOut();
        }

        const _onMessageHandler = function(data){
            _parseIncommingMsg(data);
        }

        const _initChatForm = function(){
            _cfg.loginFormEl.hide();
            _cfg.chatFormEl.show();
        }

        const _initLogInForm = function(){
            _cfg.loginFormEl.show();
            _cfg.chatFormEl.hide();
        }        

        const _signIn = function(){

            const data = {
                'action': 'logIn',
                'name': _cfg.userNameInput.val(),
                'email': _cfg.userEmailInput.val()
            }

            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            } )
            .then(response => response.json())
            .then(data => {
                if(data.hasOwnProperty('token') && data.token.length > 0){
                    _setToken(data.token);
                    _initWebSocket(data.token);
                    _initChatForm();
                }

                _handleErrors(data)
            })
        }

        const _signOut = function() {
            _initLogInForm();
            sessionStorage.removeItem('token');
            sessionStorage.removeItem('messages');
        }

        const _sendMsg = function(){
            const message = _cfg.textMsgInput.val();
            const token = _cfg.chatRoomTokenInput.val();

            _storeMessage({
                inbound: false,
                userName: 'Me',
                msg: message,
                chatRoomToken: token
            })

            _cfg.conn.send(JSON.stringify({
                'msg': message,
                'chatRoomToken': token
            }));

            _cfg.textMsgInput.val("")
            _getStorredMessagesForChatRoom(token)
        }

        const _parseIncommingMsg = function (msg) {
            if(msg === null) {
                return
            }
            const msgObj = JSON.parse(msg)

            if(msgObj.action === 'addMessage' && msgObj.msg !== '') {
                _handleIncommingMsg({
                    inbound: true,
                    userName: msgObj.user.name,
                    msg: msgObj.msg,
                    chatRoomToken: msgObj.chatRoom.token
                });
            
            } else if(msgObj.action === 'updateChatRooms' && msgObj.hasOwnProperty('chatRooms')) {
                _updateChatRooms(msgObj.chatRooms);
            
            } else if(msgObj.action === 'addUser' && msgObj.chatRoom.token !== '') {
                _addUser(msgObj);
            }
        }

        const _handleIncommingMsg = function(msgObj) {
            _cfg.chatRoomTokenInput.val(msgObj.chatRoomToken);
            _cfg.currentChatClient.text(`Chat with ${msgObj.userName}`);
            _storeMessage(msgObj);
            _getStorredMessagesForChatRoom(msgObj.chatRoomToken);
        }


        const _storeMessage = function(msgObj) {
            let messages = sessionStorage.getItem('messages');

            messages = (messages === null) ? [] : JSON.parse(messages);
            messages.unshift({
                inbound: msgObj.inbound,
                userName: msgObj.userName,
                msg: msgObj.msg,
                token: msgObj.chatRoomToken
            });

            sessionStorage.setItem('messages', JSON.stringify(messages));
        }


        const _getStorredMessagesForChatRoom = function(token){
            const messages = sessionStorage.getItem('messages');

            if(messages === null) {
                return;
            }

            const msgObj = JSON.parse(messages)
            const html = msgObj.reverse().map(msg => {
                if(msg.token === token){
                    return "<p>" + msg.msg + " from " + msg.userName + "</p>";
                }
            });

            if(_cfg.chatRoomTokenInput.val().length === 0) {
                _cfg.postedMessagesEl.html("<b>Select user to see messages</b>");
                return;
            }
            _cfg.postedMessagesEl.html(html.join(" "));

        }

        const _addUser = function(msgObj) {
            const userName = msgObj.user.name;
            const chatRoomToken = msgObj.chatRoom.token;

            const user = $('p').text(userName).attr('data-token', chatRoomToken).css('cursor', 'pointer');
            _cfg.activeUsers.append(user);
        }


        const _updateChatRooms = function(chatRooms) {
            _cfg.activeUsers.html('');

            if(Object.keys(chatRooms).length === 0) {
                return;
            }


            for(const key in chatRooms) {
                if(!chatRooms.hasOwnProperty(key)){
                    continue;
                }

                let html = "<p style='cursor: pointer' data-token='" + chatRooms[key].token + "'>" + chatRooms[key].name + "</p>"
                _cfg.activeUsers.append(html)
                _getStorredMessagesForChatRoom(chatRooms[key].token)
            }
        }


        const _handleErrors = function(data){
            if(data === undefined || !data.hasOwnProperty('errors')){
                return;
            }
            _cfg.chatErrorsEl.append(data.errors.join('<br/>'));
            _cfg.chatErrorsEl.show();
        }

    })();


    AdminChatClient.init();

</script>

</body>
</html>
