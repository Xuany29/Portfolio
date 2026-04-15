<?php
session_start();
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$user_role = $_SESSION['user_role'];
$username = $_SESSION['username'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="styles/style.css">
</head>
<body>
    <?php include 'common/header.inc'; ?>
    <div class="dashboard">

    <?php 
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "Herbarium";

    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Corrected SQL query
    $sql = "SELECT id, username, password FROM Admin_login";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $count = 1; // Initialize count for numbering rows
        while ($row = mysqli_fetch_assoc($result)) {
    ?>
        <?php if ($user_role === 'admin'){ ?>
            <h1>Welcome, Admin</h1>
            <p>You have administrative privileges. Manage the system here:</p>
            <ul>
                <li><a href="view_registration.php">Manage Users</a></li>
                <li><a href="view_enquiry.php">View Enquiries</a></li>
                <li><a href="view_contribute.php">View Contributions</a></li>
            </ul>
    </div>
    <?php
            $count++;
        }
    } 
}

    // Close the connection
    mysqli_close($conn);
    ?>
    <?php include 'common/footer.inc'; ?>
</body>
</html>
