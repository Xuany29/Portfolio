<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="description" content="Herberium Universal Form" />
        <meta name="keywords"    content="view " />
        <meta author = " Prudence Coredo" />
        <title>Admin - View Enquiries</title>
        <link rel="stylesheet" type="text/css" href="styles/style.css">
        
</head>
<body>
    
    <!-- Header -->
    <?php include_once ('common/header.inc'); ?> 
    
    <h2 class = "view_enquiry" >Enquiries</h2>
    
    <table class = "enquiry_table">
        <tr>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Street</th>
        <th>City</th>
        <th>State</th>
        <th>Postcode</th>
        <th>Tutorial</th>
        <th>Enquiry</th>
    </tr>
<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Herbarium";

// Create a connection
$conn =mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT *FROM Enquiry_Table"; // selects all the fields from the table

$result = mysqli_query($conn ,$sql); 

?>
    <?php
    // Check if there are records in the table
    if (mysqli_num_rows($result)> 0) {
        // Output data of each row

        //while($row = $result->fetch_assoc())
        while($row = mysqli_fetch_assoc($result)) {   // loops through each record and displays it in the table 

            echo "<tr>";
            //echo "<td>" . $row["fname"]. " " . $row["lname"] . "</td>"; 
            echo "<td>" . htmlspecialchars($row['fname']) . "</td>";
            echo "<td>" . htmlspecialchars($row['lname']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
            echo "<td>" . htmlspecialchars($row['srt']) . "</td>";
            echo "<td>" . htmlspecialchars($row['city']) . "</td>";
            echo "<td>" . htmlspecialchars($row['state']) . "</td>";
            echo "<td>" . htmlspecialchars($row['pcode']) . "</td>";
            echo "<td>" . htmlspecialchars($row['tutorial']) . "</td>";
            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='10'>No enquiries found</td></tr>";
    }
    $conn->close();
    ?>
</table>
<?php include_once ("common/footer.inc"); ?>
</body>
</html>
