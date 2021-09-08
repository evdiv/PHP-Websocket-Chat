<?php session_start(); 

echo '<pre>', print_r($_SESSION), '</pre>';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Client chat form</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <style>
    /* Set height of the grid so .sidenav can be 100% (adjust if needed) */
    .row.content {margin-top: 100px}
    
  </style>
</head>
<body>

<div class="container">
    <div class="row content">

        <div class="col-sm-4 col-sm-offset-4">

            <p>Client chat form</p>

            <!--Client chat Form -->
           <div class="alert alert-warning" id="postedMsgs" style="min-height: 220px;">
                 
            </div>

            <div class="form-group">
                <input type="text" 
                        id="textMsg" 
                        value="" 
                        data-user-id="<?= $_SESSION['ChatUser']['id'] ?>" 
                        placeholder="Enter text here..." 
                        class="form-control">
            </div>

            <button id="sendMsg" class="btn btn-primary btn-block">Send</button>
            <!--/Client Form -->

        </div>

  </div>
</div>


</body>
<script>
    const conn = new WebSocket('ws://localhost:8080');

    conn.onopen = function(e) {
        console.log("Connection established!");
    };

    conn.onmessage = function(e) {
        console.log(e.data);
    };

    $('#sendMsg').click(function() {
        const textMsgEl = $('#textMsg');
        const data = {
            userId: textMsgEl.data('user-id'),
            msg: textMsgEl.val()
        }
        console.log(data);
        conn.send(JSON.stringify(data));
    });


</script>

</html>