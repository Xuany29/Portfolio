<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Herberium contribution</title>
  <link rel="stylesheet" type="text/css" href="styles/style.css">
</head>
<body>
  <?php include('connection.php'); ?>
  <!-- Header -->
  <?php include_once("common/header.inc"); ?> 

  <!-- form Header -->
<div class="section">
    <h2>Upload Fresh Leaf and Specimen Images</h2>
    <div class="instructions">
        <h3>Contribute to Herberium Universal</h3>
        <p>
    <!--    <p>
         The observation can only have up to 4 pictures of the same plant.<br>
          At least one photo of leaf, flower, fruit, or bark is required for identification to work.<br>
          It is recommended to use images with these dimensions: <b>1280px</b>.-->
          Are you passionate about plants and herberiumnresearch? </br>
        Join our vibrant community of plant enthusiasts, botanists, and researchers by contributing </br> to our growing database of classified plant specimens. 
        Your expertise helps to grow vital herberium knowledge and supports global biodiversity efforts.
        </p>
   
  </div>

</br> 
<form id = "contribute" method="POST" action="contribute_process.php" novalidate = "novalidate" enctype="multipart/form-data">
  <div class="input">
    
      <label for = "plantname">  Plant Name </label><input type ="text" name ="plantname" id = "plantname" 
      maxlength="25" title="Only alphabetical characters are allowed" />
      
    <div>
      <label for="family" > Plant Family :&nbsp;&nbsp;&nbsp;</label>
      <select name="family" id="family">
          <option value="" selected>Please Select a family </option>
          <option> Dipterocarpaceae </option>
          <option> Lauraceae </option>
          <option> Burseraceae </option>
          
      </select>
    </div>

     <div> 
      <label for="genus"> Plant Genus :&nbsp;&nbsp;&nbsp;</label>
      <select name="genus" id="genus">
          <option value="" selected>Please Select a Genus </option>
          <option> Vatica </option>
          <option> Dipterocarpus </option>
          <option> Hopea </option>
          <option> Actinodaphne</option>
          <option> Endiandra  </option>
          <option> Beilschmiedia  </option>
          <option> Canarium </option>
          <option> Dacryodes  </option>
          <option> Santiria  </option>  
      </select>
    </div>

    <div> 
      <label for="genus"> Plant Species :&nbsp;</label>
      <select name="species" id="species">
          <option value="" selected>Please Select a Species </option>
          <option> Vatica mangachapoi </option>
          <option> Dipterocarpus obtusifolius</option>
          <option> Hopea aequalis </option>
          <option> Actinodaphne fragilis</option>
          <option> Endiandra hayesii </option>
          <option> Beilschmiedia roxburghiana  </option>
          <option> Canarium apertum </option>
          <option> Dacryodes costata </option>
          <option> Santiria impressinervis </option>  
      </select>
    </div>

    <div class="description-box clearfix">
      <label for="info_add_on" class="description-title">Description :&nbsp;&nbsp;</label>
      <textarea name="info_add_on" id="info_add_on" placeholder="Please write more about the plants ....."></textarea>
    </div>
    
  </div>

  <!-- Image Upload Slots -->
  <div class="section image-grid">
    <!-- Fresh Leaf Slot -->
    <label class="image-slot" for="fresh-leaf">
      <span>Click to upload Fresh Leaf</span>
      <input type="file" id="fresh_leaf" name="fresh_leaf" accept=".jpg, .png">
    </label>

    <!-- Specimen Slot -->
    <label class="image-slot" for="specimen">
      <span>Click to upload Specimen</span>
      <input type="file" id="specimen" name="specimen" accept=".jpg, .png">
    </label>
  </div>

</br>
<div class="submit">
    <button type ="submit" value = "Submit">Submit </button>
    <button type ="reset" value = "Reset">Reset</button>
</br>
</div>
</form>
</div>
<!-- Footer -->
<?php include_once("common/footer.inc"); ?>
</body>
</html>
