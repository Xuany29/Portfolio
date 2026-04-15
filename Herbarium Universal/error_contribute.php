<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<meta name="description" content="Contribution Form" />
	<meta name="keywords"    content=" " />
	<title>Contribute Confirmation Page-Error</title>
	<link rel="stylesheet" type="text/css" href="styles/style.css">
</head>

<body>
	<!-- Header -->
	<?php include_once 'common/header.inc'; ?>

	<div id="contribute_error">
		<h1>Contribute Unsuccessful !</h1>
		<?php
		$error = $_GET['error'];
		switch ($error) {
			case 'Invalid':
				echo "<p>You have empty fields or invalid messages.</p>";
				break;
			case 'Existed':
				echo "<p>Contribute already existed.</p>";
				break;
			default:
				echo "<p>Unknown error.</p>";
				break;
		} ?>
		<p>Click <a href='contribute.php'>here</a> to return to contribution page.</p>
	</div>

	<!-- Footer -->
	<?php include_once 'common/footer.inc'; ?>
</body>
</html>