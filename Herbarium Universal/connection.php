<?php
    //set servername, username and pw
    $servername = "localhost";
    $username ="root";
    $password = "";

    //create connection
    $conn = mysqli_connect($servername, $username, $password);

    //check connection
    if(!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }


    //create database
    //mysqli_query() function performs a query against a database
    $sql = "CREATE DATABASE IF NOT EXISTS Herbarium";

    mysqli_query($conn, $sql);

    mysqli_select_db($conn,"Herbarium");


// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set servername, username, and password
$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = mysqli_connect($servername, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS Herbarium";
if (!mysqli_query($conn, $sql)) {
    die("Error creating database: " . mysqli_error($conn));
}

// Select the database
if (!mysqli_select_db($conn, "Herbarium")) {
    die("Error selecting database: " . mysqli_error($conn));
}

//sql to create table
$sql = "CREATE TABLE IF NOT EXISTS Contribution_form (
    id INT (6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plantname VARCHAR(30) NOT NULL,
    family TEXT,
    genus TEXT,
    species TEXT,
    info_add_on VARCHAR(255),
    fresh_leaf LONGBLOB NOT NULL,
    specimen LONGBLOB NOT NULL
    )";
mysqli_query($conn, $sql);

$sql = "CREATE TABLE IF NOT EXISTS Enquiry_Table (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(25) NOT NULL,
    lname VARCHAR(25) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(13) NOT NULL,
    srt VARCHAR(40) NOT NULL,
    city VARCHAR(20) NOT NULL,
    state VARCHAR(50) NOT NULL,
    pcode VARCHAR(5) NOT NULL,
    tutorial VARCHAR(20) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
       )";
mysqli_query($conn, $sql);

$sql = "CREATE TABLE IF NOT EXISTS Registration_form (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(25) NOT NULL,
    lname VARCHAR(25) NOT NULL,
    username VARCHAR(255) NOT NULL,
    mail VARCHAR(255) NOT NULL,
    pword VARCHAR(255) NOT NULL
    )";
mysqli_query($conn, $sql);

$sql = "CREATE TABLE IF NOT EXISTS Admin_login (
id INT (6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) NOT NULL UNIQUE,
password VARCHAR(45) NOT NULL 
)";

mysqli_query($conn, $sql);

$admin_username = 'admin';
$admin_password = 'admin';
$admin_hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
$default_admin_query = "INSERT IGNORE INTO Admin_login (username, password) 
VALUES ('$admin_username', '$admin_hashed_password')";

mysqli_query($conn, $default_admin_query); 

// SQL to create plants table
$sql = "CREATE TABLE IF NOT EXISTS plants (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,
scientific_name VARCHAR(100),
description TEXT,
family VARCHAR(100),
genus VARCHAR(100),
species VARCHAR(100)
)";
mysqli_query($conn, $sql);

// SQL to create plant_images table
$sql = "CREATE TABLE IF NOT EXISTS plant_images (
image_id INT AUTO_INCREMENT PRIMARY KEY,
plant_id INT,
image_path VARCHAR(255) NOT NULL,
FOREIGN KEY (plant_id) REFERENCES plants(id) ON DELETE CASCADE
)";
mysqli_query($conn, $sql);

// SQL to create uploads table
$sql = "CREATE TABLE IF NOT EXISTS uploads (
id INT AUTO_INCREMENT PRIMARY KEY,
file_path VARCHAR(255) NOT NULL,
upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql);

// Insert plant data into the plants table
$sql = "INSERT INTO plants (name, scientific_name, description, family, genus, species)
VALUES
('Common basil', 'Ocimum basilicum', 'Sweet basil, a type of mint plant, originates from Asia and Africa. It is a favorite as a houseplant and flourishes with ample and regular sunlight and water. Transferring this plant from one soil environment to another is also simple. The sweet basil leaves, which are edible, can be consumed either fresh or dried, and they go well with pizza, salads, soups, teas, and numerous other dishes.', 'Mint', 'Ocimum', 'Ocimum basilicum'),
('Chinese violet', 'Asystasia gangetica', 'Chinese violet (Asystasia gangetica) is an erect or climbing plant which can reach a height of 91 cm. Its stems are weak and hairy. This is an invasive plant whose seeds disperse and spread vigorously. Native to Africa, Chinese violet can be found on almost every continent.', 'Acanthus', 'Asystasia', 'Asystasia gangetica'),
('Common coleus', 'Coleus scutellarioides', 'The common coleus, also called Coleus scutellarioides, is a plant with a diverse range of colors and leaf forms. It is native to Southeast Asia and serves as an herbal remedy in different cultures. The Mazatec people in Mexico use the common coleus as a narcotic. In Cuba, this plant is regarded as invasive.', 'Mint', 'Coleus', 'Coleus scutellarioides'),
('Mango tree', 'Mangifera indica', 'The mango tree is a big tree that hails from the Indian subcontinent. It is renowned mainly for the delicious tropical fruits it bears. In fact, the mango is the national fruit of India, Pakistan, and the Philippines. Once the mango tree has finished its fruit-bearing phase, it can be utilized for its wood. This particular kind of wood is highly valued for crafting musical instruments.', 'Cashew', 'Mangifera', 'Mangifera indica'),
('Coatbuttons', 'Tridax procumbens', 'Coatbuttons is originally from the tropical Americas and has turned into an invasive weed globally. It bears arrowhead-shaped flowers that are either yellow or white, along with hard fruits which are covered with stiff hairs. This plant is considered invasive due to the fact that each plant can produce as many as 1,500 of these hard fruits and it spreads readily, thus overpowering the native vegetation.', 'Daisy', 'Tridax', 'Tridax procumbens');
";
mysqli_query($conn, $sql);

$sql = "INSERT INTO plant_images (plant_id, image_path)
VALUES 
(1, 'images/plants/images.jpeg'),
(1, 'images/plants/images (1).jpeg'),
(1, 'images/plants/images (2).jpeg'), 
(1, 'images/plants/images (3).jpeg'),   
(1, 'images/plants/images (4).jpeg'),
(1, 'images/plants/images (5).jpeg'),    
(1, 'images/plants/images (6).jpeg'),    
(1, 'images/plants/images (7).jpeg'),    
(1, 'images/plants/images (8).jpeg'),    
(1, 'images/plants/images (9).jpeg'),    
(1, 'images/plants/images (10).jpeg'),    
(1, 'images/plants/images (11).jpeg'),    
(1, 'images/plants/images (12).jpeg'),    
(1, 'images/plants/images (13).jpeg'),    
(1, 'images/plants/images (14).jpeg'),    
(1, 'images/plants/images (15).jpeg'),    
(1, 'images/plants/images (16).jpeg'),    
(1, 'images/plants/images (17).jpeg'),    
(1, 'images/plants/images (18).jpeg'),    
(1, 'images/plants/images (19).jpeg'),    
(1, 'images/plants/download.jpeg'),    
(1, 'images/plants/download - 2024-11-19T142351.436.jpeg'),    
(1, 'images/plants/download (1).jpeg'), 
(1, 'images/plants/download (2).jpeg'),    
(1, 'images/plants/download (3).jpeg'),    
(1, 'images/plants/download (4).jpeg'),    
(1, 'images/plants/download (5).jpeg'),    
(1, 'images/plants/download (6).jpeg'),    
(1, 'images/plants/download (7).jpeg'),    
(1, 'images/plants/download (8).jpeg'),    
(1, 'images/plants/download (9).jpeg'),    
(1, 'images/plants/download (10).jpeg'),    
(1, 'images/plants/download (11).jpeg'),    
(1, 'images/plants/download (12).jpeg'),    
(1, 'images/plants/download (13).jpeg'),    
(1, 'images/plants/download (14).jpeg'),    
(1, 'images/plants/download (15).jpeg'),    
(1, 'images/plants/download (16).jpeg'),    
(1, 'images/plants/download (17).jpeg'),    
(1, 'images/plants/download (18).jpeg'),    
(1, 'images/plants/download (19).jpeg'),    
(1, 'images/plants/download (20).jpeg'),    
(1, 'images/plants/download (21).jpeg'),    
(1, 'images/plants/download (22).jpeg'),    
(1, 'images/plants/download (23).jpeg'),    
(1, 'images/plants/download (24).jpeg'),    
(1, 'images/plants/download (25).jpeg'),    
(1, 'images/plants/download (26).jpeg'),    
(1, 'images/plants/download (27).jpeg'),    
(1, 'images/plants/download (28).jpeg'),    
(1, 'images/plants/download (29).jpeg'),    
(1, 'images/plants/download (30).jpeg'),   
(2, 'images/plants/download (31).jpeg'), 
(2, 'images/plants/download (32).jpeg'),   
(2, 'images/plants/download (33).jpeg'),   
(2, 'images/plants/download (34).jpeg'),   
(2, 'images/plants/download (35).jpeg'),   
(2, 'images/plants/download (36).jpeg'),   
(2, 'images/plants/download (37).jpeg'),   
(2, 'images/plants/download (38).jpeg'),   
(2, 'images/plants/download (39).jpeg'),   
(2, 'images/plants/download (40).jpeg'),   
(2, 'images/plants/download (41).jpeg'),   
(2, 'images/plants/download (42).jpeg'),   
(2, 'images/plants/download (43).jpeg'),   
(2, 'images/plants/download (44).jpeg'),   
(2, 'images/plants/download (45).jpeg'),   
(2, 'images/plants/download (46).jpeg'),   
(2, 'images/plants/download (47).jpeg'),   
(2, 'images/plants/download (48).jpeg'),   
(2, 'images/plants/download (49).jpeg'),   
(2, 'images/plants/download (50).jpeg'),   
(2, 'images/plants/download (51).jpeg'),   
(2, 'images/plants/download (52).jpeg'),   
(2, 'images/plants/download (53).jpeg'),   
(2, 'images/plants/download (54).jpeg'),   
(2, 'images/plants/download (55).jpeg'),   
(2, 'images/plants/download (56).jpeg'),
(2, 'images/plants/download (57).jpeg'),   
(2, 'images/plants/download (58).jpeg'),   
(2, 'images/plants/download (59).jpeg'),   
(2, 'images/plants/download (60).jpeg'),   
(2, 'images/plants/download (61).jpeg'),   
(2, 'images/plants/download (62).jpeg'),
(2, 'images/plants/images (20).jpeg'),   
(2, 'images/plants/images (21).jpeg'),   
(2, 'images/plants/images (22).jpeg'),   
(2, 'images/plants/images (23).jpeg'),   
(2, 'images/plants/images (24).jpeg'),   
(2, 'images/plants/images (25).jpeg'),   
(2, 'images/plants/images (26).jpeg'),   
(2, 'images/plants/images (27).jpeg'),   
(2, 'images/plants/images (28).jpeg'),   
(2, 'images/plants/images (29).jpeg'),   
(2, 'images/plants/images (30).jpeg'),   
(2, 'images/plants/images (31).jpeg'),   
(2, 'images/plants/images (32).jpeg'),   
(2, 'images/plants/images (33).jpeg'),   
(2, 'images/plants/images (34).jpeg'),   
(2, 'images/plants/images (35).jpeg'),   
(2, 'images/plants/images (36).jpeg'),   
(2, 'images/plants/images (37).jpeg'),   
(2, 'images/plants/images (38).jpeg'),   
(2, 'images/plants/images (39).jpeg'),   
(2, 'images/plants/images (40).jpeg'),   
(2, 'images/plants/images (41).jpeg'),
(3, 'images/plants/download (63).jpeg'),  
(3, 'images/plants/download (64).jpeg'),  
(3, 'images/plants/download (65).jpeg'),  
(3, 'images/plants/download (66).jpeg'),  
(3, 'images/plants/download (67).jpeg'),  
(3, 'images/plants/download (68).jpeg'),  
(3, 'images/plants/download (69).jpeg'),  
(3, 'images/plants/download (70).jpeg'),  
(3, 'images/plants/download (71).jpeg'),  
(3, 'images/plants/download (72).jpeg'),  
(3, 'images/plants/download (73).jpeg'),  
(3, 'images/plants/download (74).jpeg'),  
(3, 'images/plants/download (75).jpeg'),  
(3, 'images/plants/download (76).jpeg'),  
(3, 'images/plants/download (77).jpeg'),  
(3, 'images/plants/download (78).jpeg'),  
(3, 'images/plants/download (79).jpeg'),  
(3, 'images/plants/download (80).jpeg'),  
(3, 'images/plants/download (81).jpeg'),  
(3, 'images/plants/download (82).jpeg'),  
(3, 'images/plants/download (83).jpeg'),  
(3, 'images/plants/download (84).jpeg'),  
(3, 'images/plants/download (85).jpeg'),  
(3, 'images/plants/download (86).jpeg'),  
(3, 'images/plants/download (87).jpeg'),  
(3, 'images/plants/download (88).jpeg'),  
(3, 'images/plants/download (89).jpeg'),  
(3, 'images/plants/download (90).jpeg'),  
(3, 'images/plants/images (42).jpeg'),  
(3, 'images/plants/images (43).jpeg'),  
(3, 'images/plants/images (44).jpeg'),  
(3, 'images/plants/images (45).jpeg'),  
(3, 'images/plants/images (46).jpeg'),  
(3, 'images/plants/images (47).jpeg'),  
(3, 'images/plants/images (48).jpeg'),  
(3, 'images/plants/images (49).jpeg'),  
(3, 'images/plants/images (50).jpeg'),  
(3, 'images/plants/images (51).jpeg'),  
(3, 'images/plants/images (52).jpeg'),  
(3, 'images/plants/images (53).jpeg'),  
(3, 'images/plants/images (54).jpeg'),  
(3, 'images/plants/images (55).jpeg'),  
(3, 'images/plants/images (56).jpeg'),  
(3, 'images/plants/images (57).jpeg'),  
(3, 'images/plants/images (58).jpeg'),  
(3, 'images/plants/images (59).jpeg'),  
(3, 'images/plants/images (60).jpeg'),  
(3, 'images/plants/images (61).jpeg'),  
(3, 'images/plants/images (62).jpeg'),  
(3, 'images/plants/images (63).jpeg'),  
(3, 'images/plants/images (64).jpeg'),  
(3, 'images/plants/images (65).jpeg'),  
(3, 'images/plants/images (66).jpeg'),  
(3, 'images/plants/images (67).jpeg'),  
(3, 'images/plants/images (68).jpeg'),  
(3, 'images/plants/images (69).jpeg'),
(4, 'images/plants/download (91).jpeg'),
(4, 'images/plants/download (92).jpeg'), 
(4, 'images/plants/download (93).jpeg'), 
(4, 'images/plants/download (94).jpeg'), 
(4, 'images/plants/download (95).jpeg'), 
(4, 'images/plants/download (96).jpeg'), 
(4, 'images/plants/download (97).jpeg'), 
(4, 'images/plants/download (98).jpeg'), 
(4, 'images/plants/download (99).jpeg'), 
(4, 'images/plants/download (100).jpeg'),
(4, 'images/plants/download - 2024-11-19T145735.346.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145737.972.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145742.557.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145745.617.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145748.678.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145751.443.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145754.416.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145758.207.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145801.669.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145805.250.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145808.489.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145811.523.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145814.454.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145817.776.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145822.997.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145825.868.jpeg'),
(4, 'images/plants/download - 2024-11-19T145828.541.jpeg'), 
(4, 'images/plants/download - 2024-11-19T145831.578.jpeg'),
(4, 'images/plants/images (70).jpeg'),
(4, 'images/plants/images (71).jpeg'),
(4, 'images/plants/images (72).jpeg'),
(4, 'images/plants/images (73).jpeg'),
(4, 'images/plants/images (74).jpeg'),
(4, 'images/plants/images (75).jpeg'),
(4, 'images/plants/images (76).jpeg'),
(4, 'images/plants/images (77).jpeg'),
(4, 'images/plants/images (78).jpeg'),
(4, 'images/plants/images (79).jpeg'),
(4, 'images/plants/images (80).jpeg'),
(4, 'images/plants/images (81).jpeg'),
(4, 'images/plants/images (82).jpeg'),
(4, 'images/plants/images (83).jpeg'),
(4, 'images/plants/images (84).jpeg'),
(4, 'images/plants/images (85).jpeg'),
(4, 'images/plants/images (86).jpeg'),
(4, 'images/plants/images (87).jpeg'),
(4, 'images/plants/images (88).jpeg'),
(4, 'images/plants/images (89).jpeg'),
(4, 'images/plants/images (90).jpeg'),
(4, 'images/plants/images (91).jpeg'),
(4, 'images/plants/images (92).jpeg'),
(4, 'images/plants/images (93).jpeg'),
(4, 'images/plants/images (94).jpeg'),
(5, 'images/plants/download - 2024-11-19T150810.489.jpeg'),
(5, 'images/plants/download - 2024-11-19T150815.930.jpeg'),
(5, 'images/plants/download - 2024-11-19T150819.299.jpeg'),
(5, 'images/plants/download - 2024-11-19T150822.231.jpeg'),
(5, 'images/plants/download - 2024-11-19T150824.741.jpeg'),
(5, 'images/plants/download - 2024-11-19T150827.606.jpeg'),
(5, 'images/plants/download - 2024-11-19T150830.572.jpeg'),
(5, 'images/plants/download - 2024-11-19T150834.113.jpeg'),
(5, 'images/plants/download - 2024-11-19T150837.011.jpeg'),
(5, 'images/plants/download - 2024-11-19T150839.876.jpeg'),
(5, 'images/plants/download - 2024-11-19T150847.099.jpeg'),
(5, 'images/plants/download - 2024-11-19T150849.937.jpeg'),
(5, 'images/plants/download - 2024-11-19T150852.591.jpeg'),
(5, 'images/plants/download - 2024-11-19T150855.574.jpeg'),
(5, 'images/plants/download - 2024-11-19T150858.827.jpeg'),
(5, 'images/plants/download - 2024-11-19T150902.169.jpeg'),
(5, 'images/plants/download - 2024-11-19T150905.083.jpeg'),
(5, 'images/plants/download - 2024-11-19T150907.791.jpeg'),
(5, 'images/plants/download - 2024-11-19T150910.504.jpeg'),
(5, 'images/plants/download - 2024-11-19T150913.947.jpeg'),
(5, 'images/plants/download - 2024-11-19T150916.709.jpeg'),
(5, 'images/plants/download - 2024-11-19T150921.167.jpeg'),
(5, 'images/plants/download - 2024-11-19T150925.227.jpeg'),
(5, 'images/plants/download - 2024-11-19T150927.967.jpeg'),
(5, 'images/plants/download - 2024-11-19T150930.573.jpeg'),
(5, 'images/plants/download - 2024-11-19T150933.411.jpeg'),
(5, 'images/plants/download - 2024-11-19T150936.369.jpeg'),
(5, 'images/plants/images (95).jpeg'),
(5, 'images/plants/images (96).jpeg'),
(5, 'images/plants/images (97).jpeg'),
(5, 'images/plants/images (98).jpeg'),
(5, 'images/plants/images (99).jpeg'),
(5, 'images/plants/images (100).jpeg'),
(5, 'images/plants/images - 2024-11-19T150959.736.jpeg'),
(5, 'images/plants/images - 2024-11-19T151002.813.jpeg'),
(5, 'images/plants/images - 2024-11-19T151005.811.jpeg'),
(5, 'images/plants/images - 2024-11-19T151008.629.jpeg'),
(5, 'images/plants/images - 2024-11-19T151012.017.jpeg'),
(5, 'images/plants/images - 2024-11-19T151016.846.jpeg'),
(5, 'images/plants/images - 2024-11-19T151020.263.jpeg'),
(5, 'images/plants/images - 2024-11-19T151023.331.jpeg'),
(5, 'images/plants/images - 2024-11-19T151026.341.jpeg'),
(5, 'images/plants/images - 2024-11-19T151029.550.jpeg'),
(5, 'images/plants/images - 2024-11-19T151033.002.jpeg'),
(5, 'images/plants/images - 2024-11-19T151037.741.jpeg'),
(5, 'images/plants/images - 2024-11-19T151041.110.jpeg'),
(5, 'images/plants/images - 2024-11-19T151044.358.jpeg'),
(5, 'images/plants/images - 2024-11-19T151048.206.jpeg'),
(5, 'images/plants/images - 2024-11-19T151051.364.jpeg'),
(5, 'images/plants/images - 2024-11-19T151054.934.jpeg'),
(5, 'images/plants/images - 2024-11-19T151057.920.jpeg'),
(5, 'images/plants/images - 2024-11-19T151102.634.jpeg'),
(5, 'images/plants/images - 2024-11-19T151105.933.jpeg'),
(5, 'images/plants/images - 2024-11-19T151111.085.jpeg'),
(5, 'images/plants/images - 2024-11-19T151114.436.jpeg'),
(5, 'images/plants/images - 2024-11-19T151119.469.jpeg'),
(5, 'images/plants/images - 2024-11-19T151124.359.jpeg');
";



mysqli_query($conn, $sql);



?>