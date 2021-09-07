<!DOCTYPE html>
<html lang="en">
<head>
  <title>Client</title>
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

            <p>Login to get support</p>

            <!--Login Form -->
            <form method="post" action="api.php?action=save">
                <div class="form-group">
                    <input type="text" name="name" placeholder="Your Name" class="form-control" required=''>
                </div>

                <div class="form-group">
                    <input type="email" name="email" placeholder="Email address" class="form-control" required=''>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Sign in</button>
            </form>
            <!--/Login Form -->

        </div>

  </div>
</div>


</body>
</html>
