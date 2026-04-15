<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<meta name="description" content="Contribution Form" />
	<meta name="keywords"    content=" " />
	<title>Login Confirmation Page</title>
	<link rel="stylesheet" type="text/css" href="./styles/style.css">
</head>

<body>

    <?php include('connection.php'); ?>
	<!-- Header -->
	<?php include_once 'common/header.inc'; ?>

<?php 
    session_start();



    if (isset($_POST['username'], $_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Check if admin
        $admin_query = "SELECT * FROM Admin_login WHERE username = ?";
        $stmt = $conn->prepare($admin_query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin_hashed_password)) {
                $_SESSION['user_role'] = 'admin';
                $_SESSION['username'] = $admin['username'];
                header("Location: view_login.php");
                exit();
            } else {
                $message = "<div class='message error'>Invalid admin password.</div>";
            }
        } else {
            // Check if user
            $user_query = "SELECT * FROM Registration_form WHERE username = ?";
            $stmt = $conn->prepare($user_query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['pword'])) {
                    $_SESSION['user_role'] = 'user';
                    $_SESSION['username'] = $user['username'];
                    header("Location: index.php");
                    $message = "<div class='message success'>Login successful.</div>";
                } else {
                    error_log("Password verification failed for user: $username");
                    $message = "<div class='message error'>*Invalid user password.</div>";
                }
            } else {
                $message = "<div class='message error'>No account found with that username.</div>";
            }
        }
        $stmt->close();
    } else {
        $message = "<div class='message error'>*Please fill in all fields.</div>";
    }
    $conn->close();
?>
<div id="message">
    <?php if (isset($message)) echo $message; ?>
</div>


<!-- Footer -->
<?php include_once 'common/footer.inc'; ?>
	
</body>
</html>