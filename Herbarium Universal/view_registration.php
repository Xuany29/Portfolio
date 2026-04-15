<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Herbarium Universal</title>
    <link rel="stylesheet" href="styles/style.css">
</head>

<body>
    <?php include_once 'common/header.inc'; ?>
    <div class="view_registration">
    <h1 id="view_registration_header">Registration</h1>

    <table id="registration_table">
        <tr>
            <th>No</th>
            <th>Username</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
        </tr>
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
    $sql = "SELECT * FROM Registration_form";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $count = 1; // Initialize count for numbering rows
        while ($row = mysqli_fetch_assoc($result)) {
?>
        <tr>
            <td><?php echo $count; ?></td>
            <td><?php echo htmlspecialchars($row["username"]); ?></td>
            <td><?php echo htmlspecialchars($row["fname"]); ?></td>
            <td><?php echo htmlspecialchars($row["lname"]); ?></td>
            <td><?php echo htmlspecialchars($row["mail"]); ?></td>
        </tr>
<?php
            $count++;
        }
    } else {
        echo "<tr><td colspan='4'>No users registered yet.</td></tr>";
    }

    // Close the connection
    mysqli_close($conn);
?>
</table>
</div>
<?php include_once 'common/footer.inc'; ?>
</body>
</html>
