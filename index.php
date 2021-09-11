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
            }
        })
    });

    const conn = new WebSocket('ws://localhost:8080');

    conn.onopen = e => console.log("Connection established!");
    conn.onmessage = e => console.log(e.data);

    $('#sendMsgBtn').click(() => {
        const textMsgEl = $('#textMsg');
        const data = {
            userId: textMsgEl.data('user-id'),
            msg: textMsgEl.val()
        }
        conn.send(JSON.stringify(data));
        textMsgEl.val("");
    });

</script>

</body>
</html>
