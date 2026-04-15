<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="description" content="Herbarium Universal Form" />
    <meta name="keywords" content="" />
    <meta name="author" content="Prudence Coredo" />
    <title>Enquiry Confirmation Page</title>
    <link rel="stylesheet" type="text/css" href="styles/style.css">
</head>
<?php 
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Herbarium";

// Create a new connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} ?>
<body>
<?php
// Start session to store errors
session_start();

// Initialize error array and form variables
$errors = [];

$fname = $lname = $email = $phone = $street = $city = $state = $postcode = $tutorial = $description = "";

// Function to sanitize input
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate first name
    if (empty($_POST['fname'])) {
        $errors['fname'] = "*First name is required.";
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $_POST['fname'])) {
        $errors['fname'] = "*Only alphabetical characters are allowed for the first name.";
        $fname = ""; // clear the input
    } else {
        $fname = test_input($_POST['fname']);
    }

    // Validate last name
    if (empty($_POST['lname'])) {
        $errors['lname'] = "*Last name is required.";
    } elseif (!preg_match("/^[A-Za-z]+$/", $_POST['lname'])) {
        $errors['lname'] = "*Only alphabetical characters are allowed for the last name.";
    } else {
        $lname = test_input($_POST['lname']);
    }

    // Validate email
    if (empty($_POST['email'])) {
        $errors['email'] = "*Email is required.";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "*Invalid email format.";
    } else {
        $email = test_input($_POST['email']);
    }

    // Validate phone number
    if (empty($_POST['phone'])) {
        $errors['phone'] = "*Phone number is required.";
    } elseif (!preg_match("/^\d{3}-\d{4}-\d{4}$/", $_POST['phone'])) {
        $errors['phone'] = "*Phone number must follow the format ###-####-####.";
    } else {
        $phone = test_input($_POST['phone']);
    }

    // Validate street address
    if (empty($_POST['srt'])) {
        $errors['street'] = "*Street address is required.";
    } else {
        $street = test_input($_POST['srt']);
    }

    // Validate city
    if (empty($_POST['city'])) {
        $errors['city'] = "*City is required.";
    } else {
        $city = test_input($_POST['city']);
    }

    // Validate state
    if (empty($_POST['state'])) {
        $errors['state'] = "*Please enter a state.";
    } else {
        $state = test_input($_POST['state']);
    }

    // Validate postcode
    if (empty($_POST['pcode'])) {
        $errors['postcode'] = "*Postcode is required.";
    } elseif (!preg_match("/^\d{5}$/", $_POST['pcode'])) {
        $errors['postcode'] = "*Postcode must be exactly 5 digits.";
    } else {
        $postcode = test_input($_POST['pcode']);
    }

    // Validate tutorial selection
    if (empty($_POST['Tutorial']) || $_POST['Tutorial'] === "") {
        $errors['tutorial'] = "*Please select a tutorial.";
    } else {
        $tutorial = test_input($_POST['Tutorial']);
    }

    // Validate description
    if (empty($_POST['enquiries'])) {
        $errors['description'] = "*Enter your Enquiry.";
    } else {
        $description = test_input($_POST['enquiries']);
    }

        if (!empty($errors)) {
    $_SESSION['errors'] = $errors;  
    $_SESSION['post_data'] = $_POST;  
    header("Location: enquiry.php");  
    exit;
    
        }else {

    // Insert data into the database
    $sql = $conn->prepare("INSERT INTO Enquiry_Table (fname, lname, email, phone, srt, city, state, pcode, tutorial, description) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $sql->bind_param("ssssssssss", $fname, $lname, $email, $phone, $street, $city, $state, $postcode, $tutorial, $description);

    if ($sql->execute()) {
        // Redirect to success page on successful submission
        header("Location: success.php");
        exit;
    } else {
        echo "Error: " . $sql->error;
    }
        }
    $sql->close();
    $conn->close();
}
?>
</body>
</html>
