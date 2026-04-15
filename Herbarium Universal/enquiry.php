 <?php 
session_start();

  // Get form data from session if available (in case of errors)
  $post_data = isset($_SESSION['post_data']) ? $_SESSION['post_data'] : [];
  $errors = isset($_SESSION['errors']) ? $_SESSION['errors'] : [];
 

  // Clear errors after displaying them
    unset($_SESSION['errors']);
    unset($_SESSION['post_data']);
  
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="description" content="Enquiry form for users" />
<meta name="keywords" content="HTML5, tags" />
<meta name="author" content="Prudence Coredo" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enquiry form</title>
<link rel="stylesheet" type="text/css" href="styles/style.css">
<!-- <style>
    .error {
        font-size: 0.9em;
    color: red !important;

    }
</style> -->
</head>
<body>
    <?php include ('connection.php'); ?>
    <!-- Header -->
    <?php include_once ('common/header.inc'); ?>
 <style>

</style>   

<!-- Form-->
<br>
    <div>
        <h2 id="Title"> Contact Us </h2>
    </div>

    <div class="contact">

    <span id="img">
    <embed src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.3913259326637!2d110.35474397496598!3d1.5324458984532114!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31fba70b11e02ce7%3A0x69cbf290cfd24bb7!2sSwinburne%20University%20of%20Technology%20Sarawak%20Campus!5e0!3m2!1sen!2smy!4v1727224969930!5m2!1sen!2smy" width="600" height="450" style="border:0;" 
        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
        aria-label="Map Showing Swinburne University of Technology Sarawak Campus">
    </span>

    <div id = "body_content">
        <p>
            <!-- Add spacing and allignment for css-->
        <h3>Location: </h3>
            Swinburne University of Technology<br/>
            Q5B, 93350 Kuching, Sarawak

            <h3>Operating Hours:</h3> 
            <h4> Weekdays:<br/>
                9 -5
            </h4>
            <h4> Weekends and public holidays <br/>
                Closed
            </h4>
            
        <table class="noStyle">
            <tbody>
                <tr>
                    <th> Telephone No. : +60 11 ....</th>
                    <td></td>
                </tr>
                <tr>
                    <th> Email : support@herbariumuniversal.com </th>
                    <td></td>
                </tr>
            </tbody>
        </table>
        </div>
    </div>        
    
    <br/>
    
    <form id = "enquiry" method="post" action="enquiry_process.php" novalidate="novalidate">

    <fieldset class="form-enquiry">
    <label for = "fname"> First Name </label><input type ="text" name ="fname" id = "fname" 
    value="<?php echo htmlspecialchars($post_data['first_name'] ?? ''); ?>"
                maxlength="25" />
                <span class="error"><?php echo isset($errors['fname']) ? $errors['fname'] : ''; ?></span></br>

    <label for = "lname"> Last Name </label><input type ="text" name ="lname" id = "lname" 
    value="<?php echo htmlspecialchars($post_data['first_name'] ?? ''); ?>"  maxlength="25" />
                <span class="error"><?php echo isset($errors['lname']) ? $errors['lname'] : ''; ?></span></br>

    <p><label>Email</label>	<input type="email" name="email" placeholder="name@domain.com" 
                                value = "<?php echo htmlspecialchars($post_data['email'] ?? ''); ?>"/>
        <span class="error"><?php echo isset($errors['email']) ? $errors['email'] : ''; ?></span></p>

    <p><label>Phone </label>
        <input type="text" name="phone" id="phone" maxlength="13"  placeholder="###-####-####"
        value = "<?php echo htmlspecialchars($post_data['phone'] ?? ''); ?>"/></br>
        <span class="error"><?php echo isset($errors['phone']) ? $errors['phone'] : ''; ?></span></p>
       </p>

        <fieldset>
            <legend class="add"> Address</legend>
            <p><label for="srt" > Street Address</label> <input type="text" name ="srt" id ="srt" maxlength="40"
            /></br>
            <span class="error"><?php echo isset($errors['street']) ? $errors['street'] : ''; ?></span></p>

            <p><label for="city"> City/ Town</label> <input type="text" name="city" id="city" maxlength="20"
            value="<?php echo htmlspecialchars($post_data['city'] ?? ''); ?>" />
            <span class="error"><?php echo isset($errors['city']) ? $errors['city'] : ''; ?></span></p>
            
    <p>
    <label for="state">State: </label>
    <select name="state" id="state">
        <option value="" selected>Please Select</option>
        <option value="Johor">Johor</option>
        <option value="Kedah">Kedah</option>
        <option value="Kelantan">Kelantan</option>
        <option value="Malacca (Melaka)">Malacca (Melaka)</option>
        <option value="Negeri Sembilan">Negeri Sembilan</option>
        <option value="Pahang">Pahang</option>
        <option value="Penang (Pulau Pinang)">Penang (Pulau Pinang)</option>
        <option value="Perak">Perak</option>
        <option value="Perlis">Perlis</option>
        <option value="Sabah">Sabah</option>
        <option value="Sarawak">Sarawak</option>
        <option value="Selangor">Selangor</option>
        <option value="Terengganu">Terengganu</option>
    </select>
    <span class="error"><?php echo isset($errors['state']) ? $errors['state'] : ''; ?></span>
</p>


        <label for="pcode"> PostCode</label> <input type="text" name="pcode" id="pcode" pattern="\d{5}" title="Enter 5 digits"
        value="<?php echo htmlspecialchars($post_data['pcode'] ?? ''); ?>" />
        <span class="error"><?php echo isset($errors['postcode']) ? $errors['postcode'] : ''; ?></span>
        </fieldset>
    

        <br/>
    <div>
        <p><label>Tutorial:</label>
            <select name="Tutorial" required="required" id="enquiry-tutorial">
                <option value="" selected>Please Select </option>
                <option >Tutorial</option>
                <option>Tools</option>
                <option> Care</option>
                
            </select></br>
            <span class="error"><?php echo isset($errors['tutorial']) ? $errors['tutorial'] : ''; ?></span></p>
            
                <h3><label>Description</label></h3>
                <span class="error"><?php echo isset($errors['description']) ? $errors['description'] : ''; ?></span></br>
                <textarea name ="enquiries"  rows="16" cols ="86" placeholder="Write descriptions of your Enquiry  here..."><?php echo htmlspecialchars($post_data['enquiries'] ?? ''); ?></textarea>
            
    </div>

    </fieldset>
    <br>
    <div class="enquiry-button">                                                                                                                                  
    <button type ="submit" value = "Submit">Submit </button>
    <button type ="reset" value = "Reset">Reset</button>
    </div> 
    <br>

    </form>
<!-- Footer -->
<?php include_once ('common/footer.inc'); ?>
</body>
</html>