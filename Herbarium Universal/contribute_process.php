<?php
session_start();

if (!isset($_SESSION['user_role'])) {
	header("Location: login.php"); // Redirect to login if not logged in
	exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<meta name="description" content="Contribution Form" />
	<meta name="keywords"    content=" " />
	<title>Contribute Confirmation Page</title>
	<link rel="stylesheet" type="text/css" href="styles/style.css">
</head>

<body>
		<?php include('connection.php'); ?>
		<!-- Header -->
		<?php include_once 'common/header.inc'; ?>

<?php
	$plantname = "";
	$family = "";
	$genus = ""; 
	$species = "";
	$info_add_on = "";
	$fresh_leaf = $_FILES['fresh_leaf'];
	$specimen = $_FILES['specimen'];


	if (isset ($_POST['plantname']) && !empty($_POST['plantname']) && preg_match("/^[a-zA-Z ]*$/", $_POST['plantname']) && strlen($_POST['plantname']) <= 25) {
		$plantname = $_POST['plantname'];
	} else {
		header("Location: error_contribute.php?error=Invalid");
		exit;
	}

	if (isset ($_POST['family']) && !empty($_POST['family'])) {
		$family = $_POST['family'];
	} else {
		header("Location: error_contribute.php?error=Invalid");
		exit;
	}

	if (isset ($_POST['genus']) && !empty($_POST['genus'])) {
		$genus = $_POST['genus'];
	} else {
		header("Location: error_contribute.php?error=Invalid");
		exit;
	}

	if (isset ($_POST['species']) && !empty($_POST['species'])) {
		$species = $_POST['species'];
	} else {
		header("Location: error_contribute.php?error=Invalid");
		exit;
	}

	if (isset ($_POST['info_add_on']) && !empty($_POST['info_add_on'])) {
		$info_add_on = $_POST['info_add_on'];
	} else {
		header("Location: error_contribute.php?error=Invalid");
		exit;
	}

?>

<div class="detail">
	<h1>Contribute Successful !</h1>

		<form id="con_Confirmform">
			<fieldset id="con_field">
				<legend>Contribution Details</legend>
					<p><strong>Plantname: </strong><?php echo $plantname; ?></p>
					<p><strong>Family: </strong><?php echo $family; ?></p>
					<p><strong>Genus: </strong><?php echo $genus; ?></p>
					<p><strong>Species: </strong><?php echo $species; ?></p>
					<p><strong>Info Add On: </strong><?php echo $_POST['info_add_on']; ?></p>
					<div class="conImage">
						<p><strong>Fresh Leaf Images: </strong><p>
						<img src = "uploadsCon/<?php echo htmlspecialchars($fresh_leaf['name']); ?>">
					</div>

					<div class="conImage">
						<p><strong>Specimen Images: </strong><p> 
						<img src = "uploadsCon/<?php echo htmlspecialchars($specimen['name']); ?>">
					</div>
			</fieldset>
		</form>
</div>


<?php
    $servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "Herbarium";
	
	$conn = mysqli_connect($servername, $username, $password, $dbname);
	if(!$conn) {
		die("Connection failed: ".mysqli_connect_error());
	}
	
    $plantname = $_POST['plantname'];
    $family = $_POST['family'];
    $genus = $_POST['genus'];
    $species = $_POST['species'];
    $info_add_on = $_POST['info_add_on'];
	$fresh_leaf = $_FILES['fresh_leaf'];
	$specimen = $_FILES['specimen'];
	
	function upload($fileName) {
		if (isset($_FILES[$fileName]) && $_FILES[$fileName]['error'] == UPLOAD_ERR_OK) {
			$targerDir = "uploadsCon/";
			$targetFile = $targerDir . basename($_FILES[$fileName]['name']);
			$fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
		}
		
		if (move_uploaded_file($_FILES[$fileName]["tmp_name"], $targetFile)) {
			return $targetFile;
		} else {
			return false;
		}
	}
	
	$fresh_leaf = upload('fresh_leaf');
	$specimen = upload('specimen');
	
	// Check for existing record
	$check_query = "SELECT * FROM Contribution_form WHERE plantname = ? AND family = ? AND genus = ? AND species = ? AND info_add_on = ? AND fresh_leaf = ? AND specimen = ?";
	$stmt = $conn->prepare($check_query);
	$stmt->bind_param("sssssss", $plantname, $family, $genus, $species, $info_add_on, $fresh_leaf, $specimen);
	$stmt->execute();
	$result = $stmt->get_result();
	
	if ($result->num_rows > 0) {
		header("Location: error_contribute.php?error=Existed");
		exit;
		
	} else {
		$sql = "INSERT INTO Contribution_form (plantname, family, genus, species, info_add_on, fresh_leaf, specimen)
				VALUES (?, ?, ?, ?, ?, ?, ?)";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("sssssss", $plantname, $family, $genus, $species, $info_add_on, $fresh_leaf, $specimen);
		$stmt->execute(); 
	}
	?>
	

	<!-- Footer -->
	<?php include_once 'common/footer.inc'; ?>
	
</body>
</html>