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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process form submission
    $id = $_POST['id'];
    $plantname = $_POST['plantname'];
    $family = $_POST['family'];
    $genus = $_POST['genus'];
    $species = $_POST['species'];
    $info_add_on = $_POST['info_add_on'];

    // Handle file uploads
    $fresh_leaf = $_FILES['fresh_leaf'];
    $specimen = $_FILES['specimen'];

    function upload($fileName) {
        if (isset($_FILES[$fileName]) && $_FILES[$fileName]['error'] == UPLOAD_ERR_OK) {
            $targetDir = "uploadsCon/";
            $targetFile = $targetDir . basename($_FILES[$fileName]['name']);
            if (move_uploaded_file($_FILES[$fileName]["tmp_name"], $targetFile)) {
                return $targetFile;
            }
        }
        return false;
    }

    $fresh_leaf_path = upload('fresh_leaf');
    $specimen_path = upload('specimen');

    // Update query
    $sql = "UPDATE Contribution_form SET plantname = ?, family = ?, genus = ?, species = ?, info_add_on = ?";
    if ($fresh_leaf_path) {
        $sql .= ", fresh_leaf = ?";
    }
    if ($specimen_path) {
        $sql .= ", specimen = ?";
    }
    $sql .= " WHERE id = ?";

    $stmt = $conn->prepare($sql);
    if ($fresh_leaf_path && $specimen_path) {
        $stmt->bind_param("sssssssi", $plantname, $family, $genus, $species, $info_add_on, $fresh_leaf_path, $specimen_path, $id);
    } elseif ($fresh_leaf_path) {
        $stmt->bind_param("ssssssi", $plantname, $family, $genus, $species, $info_add_on, $fresh_leaf_path, $id);
    } elseif ($specimen_path) {
        $stmt->bind_param("ssssssi", $plantname, $family, $genus, $species, $info_add_on, $specimen_path, $id);
    } else {
        $stmt->bind_param("sssssi", $plantname, $family, $genus, $species, $info_add_on, $id);
    }

    if ($stmt->execute()) {
        header("Location: view_contribute.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
    $conn->close();
} else {
    // Display form with current data
    $id = $_GET['id'];
    $sql = "SELECT * FROM Contribution_form WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Contribution</title>
    <link rel="stylesheet" type="text/css" href="styles/style.css">
</head>
<body>
    <?php include_once 'common/header.inc'; ?>
    <div class="edit_contribution">
        <h1>Edit Contribution</h1>
        <form action="edit.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <label for="plantname">Plant Name:</label><br>
            <input type="text" id="plantname" name="plantname" value="<?php echo htmlspecialchars($row['plantname']); ?>" required><br>
            <label for="family">Family:</label><br>
            <input type="text" id="family" name="family" value="<?php echo htmlspecialchars($row['family']); ?>" required><br>
            <label for="genus">Genus:</label><br>
            <input type="text" id="genus" name="genus" value="<?php echo htmlspecialchars($row['genus']); ?>" required><br>
            <label for="species">Species:</label><br>
            <input type="text" id="species" name="species" value="<?php echo htmlspecialchars($row['species']); ?>" required><br>
            <label for="info_add_on">Additional Information:</label><br>
            <textarea id="info_add_on" name="info_add_on" required><?php echo htmlspecialchars($row['info_add_on']); ?></textarea><br>
            <label for="fresh_leaf">Fresh Leaf Image:</label>
            <input type="file" id="fresh_leaf" name="fresh_leaf"><br>
            <label for="specimen">Specimen Image:</label>
            <input type="file" id="specimen" name="specimen"><br>
            <input type="submit" value="Update">
        </form>
    </div>
    <?php include_once 'common/footer.inc'; ?>
</body>
</html>

<?php
}
?>