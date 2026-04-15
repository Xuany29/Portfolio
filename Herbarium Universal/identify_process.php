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

// Directory to store uploads
$targetDir = "/Applications/XAMPP/htdocs/yuxuan_assign2/uploads/";

// Check if a file was uploaded
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"]) && $_FILES["image"]["error"] == UPLOAD_ERR_OK) {
    // Generate a unique name for the uploaded file to prevent overwriting existing files
    $targetFile = $targetDir . uniqid() . "_" . basename($_FILES["image"]["name"]);

    // Move the uploaded file to the "uploads" directory
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        // File successfully uploaded

        // Insert file path into the uploads table in the database
        $filePath = basename($targetFile);
        $sql = "INSERT INTO uploads (file_path) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $filePath);

        if ($stmt->execute()) {
            // Proceed with Plant.id API identification

            // Prepare to send the image to the Plant.id API
            $apiKey = '0Esc7hZjzODI89MapuWldR1O2Hmyt1lrfrCnyyugxf9aHI78xY';
            $apiUrl = 'https://api.plant.id/v2/identify'; // Corrected API endpoint

            // Set up the data to send to the API
            $data = [
                'images' => new CURLFile($targetFile),
                'organs' => 'leaf' // Example organ, adjust as needed
            ];

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Api-Key: ' . $apiKey,
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'images' => new CURLFile($targetFile),
                'organs' => 'leaf'
            ]);

            // Execute cURL and get the response
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'cURL error: ' . curl_error($ch);
                exit();
            }

            // Get the HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Log the response
            file_put_contents('plant_id_api_response.log', "HTTP Code: $httpCode\nResponse: $response\n");

            // Close cURL
            curl_close($ch);

            // Check if the response is valid JSON
            $responseData = json_decode($response, true);
            if ($responseData === null) {
                echo 'Failed to decode JSON response: ' . json_last_error_msg();
                exit();
            }

            // Process the API response
            if (isset($responseData['suggestions']) && !empty($responseData['suggestions'])) {
                // Get the most probable plant name, convert it to lowercase, and trim any extra spaces
                $plantName = strtolower(trim($responseData['suggestions'][0]['plant_details']['scientific_name']));
                $plantName = preg_replace('/\s+/', ' ', $plantName); // Replace multiple whitespaces with a single space

                // Log the queried plant name for debugging
                file_put_contents('plant_name_debug.log', "Plant Name Queried: $plantName\n");

                // Query the database to get information about the predicted plant using case-insensitive matching
                $sql = "SELECT * FROM plants WHERE LOWER(scientific_name) = ?";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    echo "Failed to prepare the statement: " . $conn->error;
                    exit();
                }

                $stmt->bind_param("s", $plantName);
                $stmt->execute();
                $resultData = $stmt->get_result();

                if ($resultData->num_rows > 0) {
                    $plant = $resultData->fetch_assoc();
                    // Redirect to the view page with the plant details
                    header("Location: identify_view.php?scientific_name=" . urlencode($plant['scientific_name']));
                } else {
                    // Log for debugging if no result found
                    file_put_contents('plant_name_debug.log', "No matching plant found in the database for: $plantName\n", FILE_APPEND);

                    // Redirect with no plant found message
                    header("Location: identify_view.php?result=not_found");
                }
            } else {
                // No plant identified by the API
                header("Location: identify_view.php?result=not_found");
            }
            exit();
        } else {
            echo "Failed to store upload information in database.";
        }
    } else {
        echo "Sorry, there was an error uploading your file.<br>";
        echo "Error code: " . $_FILES["image"]["error"] . "<br>";
        echo "Target directory is: " . $targetDir;
    }
} else {
    echo "No valid file uploaded.";
}

mysqli_close($conn);
?>
