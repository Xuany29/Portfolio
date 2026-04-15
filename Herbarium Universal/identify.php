<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Herbarium Universal</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <!-- Header -->
    <?php include_once 'common/header.inc'; ?>

    <!-- Authentication Section -->
    <?php 
    session_start();
    
    // Check if the user has confirmed they are human
    if (!isset($_SESSION['authenticated'])) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['human_check']) && $_POST['human_check'] == 'yes') {
            $_SESSION['authenticated'] = true;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
        ?>
        <div class="human-check">
            <h1>Are you human?</h1>
            <form action="" method="POST">
                <label for="human_check_yes">Yes</label>
                <input type="radio" id="human_check_yes" name="human_check" value="yes" required>
                <br>
                <button type="submit">Submit</button>
            </form>
        </div>
        <?php
        include_once 'common/footer.inc';
        exit;
    }
    ?>

    <!-- Identify Page -->
    <!-- Image Upload Area -->
    <div class="identify-area">
            <h1>Plant Identification</h1>
            <h2>Rules of Plant Identification:</h2>
            <div class="identify-guide">
                <dl><strong>Limit to 4 pictures:</strong>Please only upload 1 picture for the identification of a single plant.</dl>
                <p><strong>One Leaf, Flower, Fruit, or Bark:</strong>To enhance the success rate of plant identification, it is highly recommended to include the leaf, flower, fruit, or bark of the plant.</p>
                <p><strong>Recommendation to Image Dimension:</strong> For more accurate identification results, it is also advisable to use images with a width of 1280 pixels.</p>
                <figure>
                    <img src="./images/WhatsApp_Image_2024-10-01_at_20.42.07-removebg.png" alt="">
                </figure>
            </div>
            <div class="upload-card">
                <div class="frame">
                    <img src="./images/monitor.png" alt="">
                    <form id="uploadForm" action="identify_process.php" method="POST" enctype="multipart/form-data">
                        <div class="upload-button">
                            <label for="fileInput">
                                <figure>
                                    <img src="./images/upload.png" alt="">
                                </figure>
                                <p>Upload a Picture</p>
                            </label>
                            <input type="file" id="fileInput" name="image" accept="image/*" onchange="document.getElementById('uploadForm').submit();">
                        </div>
                    </form>
                    <div class="drop-picture-text">
                        <p>Choose a plant you want to identify </p>
                    </div>
                </div>
                <div class="picture-note">
                    <span><strong>Note:</strong> Please ensure the image of your plant is as clear as possible, and avoid being too far or too close.</span>
                </div>
            </div>
    </div>

    <!-- Text Area -->
    <div class="text-area">
        <div class="container clearfix">
            <div class="main-topic leftfix">
                <h2>The Wonders of Herbariums: Preserving Plant Diversity</h2>
                <br>
            </div>
    
            <div class="sub-content leftfix">
                <p>Plants, with their astounding diversity of around 400,000 known species on Earth, are a marvel of nature. </p>
                <p>Green plants not only provide a significant portion of the world's molecular oxygen but also form the foundation of most of Earth's ecosystems.</p>
                <p>Herbariums are invaluable tools for plant identification. Taxonomists often compare newly collected plant specimens to those in herbariums to confirm the species identity. </p>
                <p>By understanding the importance of herbariums and learning the proper methods of collection and identification, you can contribute to the preservation of global plant biodiversity. </p>
                <span>There are four main groups of plants in the plant kingdom:
                    <ol>
                        <li><strong>Angiosperms</strong> — Flowering plants</li>
                        <li><strong>Gymnosperms</strong> — Conifers and cycads</li>
                        <li><strong>Pteridophytes</strong> — Ferns</li>
                        <li><strong>Bryophytes</strong> — Mosses and liverworts</li>
                    </ol>
                </span>
            </div>

            <div class="text-image clearfix">
                <span class="plant-image leftfix">
                    <img src="./images/Group-1@2x.png" alt="">
                </span>
            </div>
        </div>
            
     </div>

    <!-- Identify Gallery Wrap Picture -->
   <div class="gallery-wrap-pc">
        <div class="gallery-wrap-title">
            <div class="gallery-wrap-text1">Identify Toxic Plants</div>
            <div class="gallery-wrap-text2"><em>Distinguish various poisonous plants, flowers, and trees around you</em></div>
        </div>
        <div class="plants-items">
            <a href="aloevera.php" class="plants-items-card">
                <img src="images/aloevera.webp" alt="" width="291px" height="160px">
                <div class="plants-items-card-content">
                    <div class="plants-items-card-content-title">Aloe vera</div>
                    <span  class="plants-items-card-content-text">Sometimes called Chinese aloe, Wand of heaven, or Burn aloe, is an evergreen succulent originating from the Arabian Peninsula. Its thick leaves contain a gelatinous substance that serves as a water reservoir, enabling it to endure in its arid native habitats. This plant has gained significant popularity as a houseplant and is widely utilized in numerous cosmetics and skincare products.</span>
                </div>
                <div class="plants-items-card-content-read-more">
                    <div class="plants-items-card-content-read-more-text">Read more &#8594;</div>
                </div>
            </a>

            <a href="birdofparadise.php" class="plants-items-card">
                <img src="images/birdofparadise.webp" alt="" width="291px" height="160px">
                <div class="plants-items-card-content">
                    <div class="plants-items-card-content-title">Bird of Paradise</div>
                    <span  class="plants-items-card-content-text">Referred to as Mini craneflower or Queen's bird-of-paradise, is a vivid, blooming plant. It hails from South Africa and holds significance in the national culture there, to the extent that it is depicted on the country's 50 - cent coin. Despite not being native, it has become the official flower of Los Angeles. In their natural habitat, these flowers draw sunbirds for pollination.</span>
                </div>
                <div class="plants-items-card-content-read-more">
                    <div class="plants-items-card-content-read-more-text">Read more &#8594;</div>
                </div>
            </a>

            <a href="blackcherry.php" class="plants-items-card">
                <img src="images/blackcherry.webp" alt="" width="291px" height="160px">
                <div class="plants-items-card-content">
                    <div class="plants-items-card-content-title">Black Cherry</div>
                    <span  class="plants-items-card-content-text">Alternatively named Whisky cherry or American cherry (Prunus serotina), is a medium-sized deciduous forest tree indigenous to the Americas and naturalized in certain European regions. The bark of mature specimens is grayish to black and characteristically cracked. The glossy foliage is poisonous to livestock. It is a highly reproductive pioneer species with invasive capabilities.</span>
                </div>
                <div class="plants-items-card-content-read-more">
                    <div class="plants-items-card-content-read-more-text">Read more &#8594;</div>
                </div>
            </a>

            <a href="taro.php" class="plants-items-card">
                <img src="images/taro.webp" alt="" width="291px" height="160px">
                <div class="plants-items-card-content">
                    <div class="plants-items-card-content-title">Taro</div>
                    <span  class="plants-items-card-content-text">Known by various names such as Yam, Madhumbe, Magogoya, Arbi, Caladium, Malanga, Coco yam, and Dasheen (scientific name Colocasia esculenta), is a tropical plant originating from southern India and Southeast Asia. It is typically cultivated as a root vegetable and has numerous culinary applications.</span>
                </div>
                <div class="plants-items-card-content-read-more">
                    <div class="plants-items-card-content-read-more-text">Read more &#8594;</div>
                </div>
            </a>

        </div>
   </div>

    <!-- Footer -->
    <?php include_once 'common/footer.inc'; ?>
</body>
</html>
