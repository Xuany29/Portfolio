<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Herbarium Universal</title>
    <link rel="stylesheet" href="styles/style.css"> 
</head>
<body>

    <?php include ('connection.php')?>
    <!-- Header -->
    <?php include_once 'common/header.inc'; ?>
    <form method="post" action="registration_process.php" novadidate="novalidate">

    <div class="box-registration">
        <h2>Register</h2>

        <form id="detail" method="post" novalidate>
          <div class="form group">
            <label for="fname">First Name:</label>
            <input type="text" id="fname-registration" name="fname" maxlength="25"><br>
            <label for="lname">Last Name:</label>
            <input type="text" id="lname-registration" name="lname" maxlength="25"><br>
            <label for="username">Username:</label>
            <input type="text" id="username-registration" name="username" maxlength="25"><br>
            <label for="mail">Email Address:</label>
            <input type="email" id="mail" name="mail"><br>
            <label for="pword">Password:</label>
            <input type="password" id="pword" name="pword" maxlength="25"><br>
          </div>

          <div class="register-btn">
            <input type="submit" value="Register">
          </div>
          <p class="p">Other ways to register</p><br>
          <div class="social-login">
            <img src="images/wechat_logo.png" alt="WeChat" title="Register with WeChat">
            <img src="images/google_logo.png" alt="Google" title="Register with Google">
          </div>
        </form>
    </div>
     <!-- Footer -->
     <?php include_once 'common/footer.inc'; ?>
     
</body>
</html>