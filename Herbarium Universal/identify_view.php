<?php
// Set up the database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Herbarium";

$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set to utf8
$conn->set_charset("utf8");

// Enable error reporting for better debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if a scientific name is provided
if (isset($_GET['scientific_name'])) {
    $plantScientificName = urldecode($_GET['scientific_name']);
    $plantScientificName = strtolower(trim($plantScientificName)); // Convert to lowercase and trim any extra spaces

    // Log the plant name for debugging
    file_put_contents('identify_view_debug.log', "Plant Scientific Name Queried: $plantScientificName\n");

    // Fetch plant information based on the scientific name (case-insensitive match)
    $sql = "SELECT * FROM plants WHERE LOWER(scientific_name) = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Database error: " . $conn->error;
        exit();
    }

    $stmt->bind_param("s", $plantScientificName);
    $stmt->execute();
    $resultData = $stmt->get_result();

    // HTML header
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plant Identification Result</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>';

    if ($resultData->num_rows > 0) {
        $plant = $resultData->fetch_assoc();
        
        // Display the plant information
        echo "<div class='plant-info'>";
        echo "<h1>Identification Result</h1>";
        echo "<h2>" . htmlspecialchars($plant['name']) . " (" . htmlspecialchars($plant['scientific_name']) . ")</h2>";
        echo "<p><strong>Description:</strong> " . htmlspecialchars($plant['description']) . "</p>";
        echo "<p><strong>Family:</strong> " . htmlspecialchars($plant['family']) . "</p>";
        echo "<p><strong>Genus:</strong> " . htmlspecialchars($plant['genus']) . "</p>";
        echo "<p><strong>Species:</strong> " . htmlspecialchars($plant['species']) . "</p>";
        echo "</div>";

        // Fetch related images from the plant_images table
        $plantId = $plant['id'];
        $sqlImages = "SELECT image_path FROM plant_images WHERE plant_id = ? LIMIT 5"; // Limit to 5 images
        $stmtImages = $conn->prepare($sqlImages);
        if (!$stmtImages) {
            echo "Database error: " . $conn->error;
            exit();
        }

        $stmtImages->bind_param("i", $plantId);
        $stmtImages->execute();
        $imagesResult = $stmtImages->get_result();

        if ($imagesResult->num_rows > 0) {
            echo "<div class='plant-images'>";
            echo "<h3>Related Images:</h3>";
            while ($image = $imagesResult->fetch_assoc()) {
                echo "<img src='/YuXuan/" . htmlspecialchars($image['image_path']) . "' alt='Plant Image'>";
            }
            echo "</div>";
        } else {
            echo "<div class='plant-info'><p>No images available for this plant.</p></div>";
        }

    } else {
        echo "<div class='plant-info'>";
        echo "<h1>Identification Result</h1>";
        echo "<p>Plant not found in the database.</p>";
        echo "</div>";

        // Log the failure for debugging purposes
        file_put_contents('identify_view_debug.log', "No matching plant found in the database for: $plantScientificName\n", FILE_APPEND);
    }

    // Close the database connection
    mysqli_close($conn);

// HTML footer
echo '
</body>
</html>';
} else {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Result Found</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <div class="plant-info">
        <h1>No result to display.</h1>
        <p>Please try identifying another plant.</p>
    </div>
</body>
</html>';
}
?>
