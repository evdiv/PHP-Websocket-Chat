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

                <div class="alert alert-warning" id="postedMsgs" style="min-height: 220px;"></div>

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
            textMsgInput:       $('#textMsg'),
            chatRoomTokenInput: $('#chatRoomToken'),
            sendMsgButton:      $('#sendMsgBtn'),
            chatErrorsEl:       $('#chatErrors'),
            exitChatButton:     $('#exitChatBtn')
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
                _cfg.chatRoomTokenInput.val(chatRoomToken);
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
        }

        const _sendMsg = function(){
            const message = _cfg.textMsgInput.val();
            const token = _cfg.chatRoomTokenInput.val();

            const data = {
                'msg': message,
                'chatRoomToken': token
            }

            _cfg.conn.send(JSON.stringify(data));
            _cfg.textMsgInput.val("");
        }

        const _parseIncommingMsg = function (msg) {
            if(msg === null) {
                return
            }

            const msgObj = JSON.parse(msg)

            if(msgObj.action === 'addMessage' && msgObj.msg !== '') {
                _addNewMessage(msgObj);
            
            } else if(msgObj.action === 'addUser' && msgObj.chatRoom.token !== '') {
                _addUser(msgObj);
            }
        }

        const _addNewMessage = function(msgObj) {
            const userName = msgObj.user.name;
            const textMsg = msgObj.msg;
            const chatRoomToken = msgObj.chatRoom.token;

            _cfg.postedMessagesEl.append("<p>" + textMsg + " from " + userName + "</p>");
            _cfg.chatRoomTokenInput.val(chatRoomToken);
        }


        const _addUser = function(msgObj) {
            const userName = msgObj.user.name;
            const chatRoomToken = msgObj.chatRoom.token;

            const user = $('p').text(userName).attr('data-token', chatRoomToken).css('cursor', 'pointer');
            _cfg.activeUsers.append(user);
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
