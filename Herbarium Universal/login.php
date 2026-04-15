<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="description" content=" " />
  <meta name="keywords" content=" " />
  <meta name="author" content=" "  />
  <title>Login Form</title>
  <link rel="stylesheet" type="text/css" href="./styles/style.css">
</head>
<body class="border">
<?php include ('connection.php');?>
<?php include_once 'common/header.inc'; ?>

        <div class="content"></div>
        
        <div class="login-box">
            <div class="qr-login">
                <h2>Log in with QR</h2>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Example" alt="QR Code">
                <p class="align">Please use WeChat to scan the QR code to log in</p>
            </div>

            <div class="divider"></div>

            <div class="password-login">
                <h2>Password Login</h2>
                <form id="detail" method="post" action="login_process.php" novalidate>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" maxlength="45" required>
                    </div>
                    <div class="button">
                        <button type="submit">Login</button>
                    </div>
                </form>
                
            </div>
        </div>



    <?php include_once("common/footer.inc"); ?>
</body>
</html>