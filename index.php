<!DOCTYPE html>
<html lang="en">
<head>
  <title>Client</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>

<div class="container">
    <div class="row content">



        <div class="col-sm-4 col-sm-offset-4">
            <!--Errors -->
            <div id="chatErrors" class="alert alert-danger" style="display:none;"></div>
            <!--/Errors-->

            <div id='loginForm'>
                <p>Login to get support</p>

                <!--Login Form -->
                <div class="form-group">
                    <input type="text" id="userName" placeholder="Your Name" class="form-control">
                </div>

                <div class="form-group">
                    <input type="email" id="userEmail" placeholder="Email address" class="form-control">
                </div>

                <button id="signInBtn" class="btn btn-primary btn-block">Sign in</button>
                <!--/Login Form -->
            </div>


            <div id='chatForm' style="display:none;">

                <button type="button" id="exitChatBtn" class="close" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>

                <p>Client chat form</p>

                <!--Client chat Form -->
               <div class="alert alert-warning" id="postedMsgs" style="min-height: 220px;"></div>

                <div class="form-group">
                    <input type="text" 
                            id="textMsg" 
                            value="" 
                            placeholder="Enter text here..." 
                            class="form-control">
                </div>


                <button id="sendMsgBtn" class="btn btn-primary btn-block">Send</button>
                <!--/Client Form -->
            </div>

        </div>
  </div>
</div>

<script>

const ChatClient = new(function () {
    const _root = this;
    let _cfg = {
        token:              '',
        connection:         null,
        postedMessagesEl:   $('#postedMsgs'),
        loginFormEl:        $('#loginForm'),
        userNameInput:      $('#userName'),
        userEmailInput:     $('#userEmail'),
        signInButton:       $('#signInBtn'),
        chatFormEl:         $('#chatForm'),
        textMsgInput:       $('#textMsg'),
        sendMsgButton:      $('#sendMsgBtn'),
        chatErrorsEl:       $('#chatErrors'),
        exitChatButton:     $('#exitChatBtn')
    }

    _root.init = function(options){
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
            _getStorredMessages();
            _cfg.textMsgInput.val("");

        })

        _cfg.exitChatButton.on('click', () => {
            _cfg.conn.close();
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
        _getStorredMessages();
    }

    const _onCloseHandler = function(){
        console.log("Connection closed!");
        _signOut();
    }

    const _onMessageHandler = function(msg){
        _handleIncommingMsg(msg);
        _getStorredMessages();
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
        if(message === ''){
            return;
        }

        _cfg.conn.send(message);
        _storeMessage({'msg': message, 'user':{'name': 'Me'}});
    }


    const _handleIncommingMsg = function (msg) {
        if(msg === null) {
            return
        }

        const data = JSON.parse(msg)
        if(data.action === 'addMessage' && data.msg !== '') {
            _storeMessage(data);
        }
    }


    const _storeMessage = function(data) {
        let messages = sessionStorage.getItem('messages');

        messages = (messages === null) ? [] : JSON.parse(messages);
        messages.unshift(data);

        sessionStorage.setItem('messages', JSON.stringify(messages));
    }


    const _getStorredMessages = function(){
        const messages = sessionStorage.getItem('messages');

        if(messages === null) {
            return;
        }

        const data = JSON.parse(messages)
        const html = data.reverse().map(msg => {
            return "<p>" + msg.msg + " from " + msg.user.name + "</p>";
        });

        _cfg.postedMessagesEl.html(html.join(" "));

    }


    const _handleErrors = function(data){
        if(data === undefined || !data.hasOwnProperty('errors')){
            return;
        }
        _cfg.chatErrorsEl.append(data.errors.join('<br/>'));
        _cfg.chatErrorsEl.show();
    }

})();


ChatClient.init();

</script>

</body>
</html>
