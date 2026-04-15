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

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare the delete statement
    $sql = "DELETE FROM Contribution_form WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Record deleted successfully";
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "No ID specified";
}

mysqli_close($conn);

// Redirect back to the view page
header("Location: view_contribute.php");
exit();
?>