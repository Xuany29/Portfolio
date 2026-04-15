<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Herbarium Universal</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<?php include('connection.php'); ?>
    <?php 
        
        if (isset($_POST['fname']) && !empty($_POST['fname'])){
                $fname = $_POST['fname'];
            }
        else{
            require 'error_registration.php';
            exit;
            }
            if (isset($_POST['lname']) && !empty($_POST['lname'])){
                $lname = $_POST['lname'];
            }
        else{
            require 'error_registration.php';
            exit;
            }
            if (isset($_POST['username']) && !empty($_POST['username'])){
                $username = $_POST['username'];
            }
        else{
            require 'error_registration.php';
            exit;
            }
            if (isset($_POST['mail']) && !empty($_POST['mail'])){
                $mail = $_POST['mail'];
            }
        else{
            require 'error_registration.php';
            exit;
            }
            if (isset($_POST['pword']) && !empty($_POST['pword'])){
                $pword= $_POST['pword'];
            }
        else{
            require 'error_registration.php';
            exit;
            }  
    ?>

<?php
    $servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "Herbarium";

	$conn = mysqli_connect($servername, $username, $password, $dbname);
	if(!$conn) {
		die("Connection failed: ".mysqli_connect_error());
	}

	//get value confirm.php
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $username = $_POST['username'];
    $mail = $_POST['mail'];
    $pword = $_POST['pword'];

    $hashed_password = password_hash($pword, PASSWORD_DEFAULT);

	$sql = "INSERT INTO Registration_form (fname, lname, username, mail, pword)
	        VALUES('$fname', '$lname', '$username', '$mail', '$hashed_password')";

    if (mysqli_query($conn, $sql)) {
        // Redirect to homepage.php on success
        header("Location: index.php");
        exit; // Ensure no further code is executed after redirection
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }


	mysqli_close($conn);
?>

</body>
</html>
