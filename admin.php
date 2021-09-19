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
                <h3>Admin chat form</h3>

                <div class="alert alert-warning" id="postedMsgs" style="min-height: 220px;"></div>

                <div class="form-group">
                    <input type="text" 
                            id="textMsg" 
                            value="" 
                            placeholder="Enter text here..." 
                            class="form-control">

                    <input type="text" 
                            id="chatRoomToken" 
                            value="" 
                            class="form-control">        
                </div>

                <button id="sendMsgBtn" class="btn btn-primary btn-block">Send</button>

            </div>
        </div><!--/#chatForm -->
  </div>
</div>

<script>

    const parseIncommingMsg = function (msg) {
        if(msg === null) {
            return
        }

        const msgObj = JSON.parse(msg)

        if(msgObj.action === 'addUser') {
            const userName = msgObj.user.name;
            const chatRoomToken = msgObj.chatRoom.token;

            const user = $('p').text(userName).attr('data-token', chatRoomToken).css('cursor', 'pointer');
            $('#activeUsers').append(user);
        
        } else if(msgObj.action === 'addMessage') {
            const userName = msgObj.user.name;
            const chatRoomToken = msgObj.chatRoom.token;
            const textMsg = msgObj.msg;

            $('#postedMsgs').append("<p>" + textMsg + " from " + userName + "</p>");
            $('#chatRoomToken').val(chatRoomToken);
        }

        console.log(msg);
    }


    $('#signInBtn').click(() => {
        const userName = $('#userName').val(), 
              userEmail = $('#userEmail').val();

        const data = {
            'action': 'logIn',
            'name': userName,
            'email': userEmail
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
            if(data.token.length > 0){
                $('#loginForm').hide();
                $('#chatForm').show();

                sessionStorage.setItem('token', data.token);
                
                conn = new WebSocket('ws://localhost:8080?token='+data.token);

                conn.onopen = e => console.log("Connection established!");
                conn.onclose = e => sessionStorage.removeItem('token');
                conn.onmessage = e => parseIncommingMsg(e.data);
            }
        })
    });


    $('#activeUsers').click((el) => {
        const chatRoomToken = $(el.target).attr('data-token');
        $('#chatRoomToken').val(chatRoomToken);
    })


    $(document).ready(() => {
        const token = sessionStorage.getItem('token');
        if(token){
            $('#loginForm').hide();
            $('#chatForm').show();

            const conn = new WebSocket('ws://localhost:8080?token=' + token);

            conn.onopen = e => console.log("Connection established!");
            conn.onclose = e => sessionStorage.removeItem('token');
            conn.onmessage = e => parseIncommingMsg(e.data);

            $('#sendMsgBtn').click(() => {
                const textMsgEl = $('#textMsg')
                const tokenEl = $('#chatRoomToken')

                const data = {
                    'msg': textMsgEl.val(),
                    'chatRoomToken': tokenEl.val()
                }

                conn.send(JSON.stringify(data));
                textMsgEl.val("");
            });
        }
    });




</script>

</body>
</html>
