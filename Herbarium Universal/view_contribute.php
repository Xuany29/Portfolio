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
    <title>View Contribution</title>
    <meta charset="uts-8"/>
    <meta name="author" content=""/>
    <meta name="description" content=""/>
    <meta name="keywords" content=""/>
    <link rel="stylesheet" type="text/css" href="styles/style.css">

</head>

<body>
    <?php include_once 'common/header.inc'; ?>

<div id="contribute_view">
<h1 id="contribute_view_header">Contribution</h1>

<table id="contribute_view_table">
    <tr>
        <th>No</th>
        <th>Plant Name</th>
        <th>Family</th>
        <th>Genus</th>
        <th>Species</th>
        <th>Description</th>
        <th>Fresh Leaf</th>
        <th>Specimen</th>
        <th></th>
    </tr>

<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "Herbarium";

    $conn = mysqli_connect($servername, $username, $password, $dbname);

    $sql = "SELECT * FROM Contribution_form";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
    //output data of each row
    while($row = mysqli_fetch_assoc($result)) {
?>

        <tr>
        <td> <?php echo $row["id"]; ?> </td>
        <td> <?php echo $row["plantname"]; ?> </td>
        <td> <?php echo $row["family"]; ?> </td>
        <td> <?php echo $row["genus"]; ?> </td>
        <td> <?php echo $row["species"]; ?> </td>
        <td> <?php echo $row["info_add_on"]; ?> </td>
        <td> <img src = "<?php echo $row["fresh_leaf"]; ?>" class="conImage_view"> </td>
        <td> <img src = "<?php echo $row["specimen"]; ?>" class="conImage_view"> </td>
        <td> 
            <div class="button_edit">
                <a href="edit.php?id=<?php echo $row['id']; ?>">Edit</a>
            </div> 
            <div class="button_delete">
                <a href="delete.php?id=<?php echo $row['id']; ?>">Delete</a>
            </div>
        </td>
    </tr>


<?php
        }
    }else { ?>
        <tr>
            <td colspan="9">No records found</td>
        </tr>
<?php
    }

    mysqli_close($conn);
?>
</table>
</div>
<?php include_once 'common/footer.inc'; ?>
