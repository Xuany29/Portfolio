<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Plant Classification</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <!-- Header -->
    <?php include_once 'common/header.inc'; ?>
    
    <!-- Plant Classification Page -->
    <div class="classify-area">
        <h1>Classification of Plant</h1>
        <div class="classify-brief">
            <div class="classify-brief-text">
                <dl>In biological classification, plants are organized into hierarchical categories to better understand their relationships and characteristics. These categories include Family, Genus, and Species.</dl>
                <ol>
                    <li><strong><em>Family:</em></strong> A group of related plants that share broad similarities.</li>
                    <li><strong><em>Genus:</em></strong> A group within the family that contains plants with more specific similarities.</li>
                    <li><strong><em>Species:</em></strong> The most specific category, which refers to individual plants that can reproduce and share common traits.</li>
                </ol>
            </div>
            <figure>
                <img src="./images/Relationship_Illustration-removebg-preview.png" alt="">
                <caption><em>Fig. Relationship Illustration</em></caption>
            </figure>
        </div>
        

        <div class="classify-card">
            <div class="family-frame">
                <div class="inner">
                    <div class="front">
                        <figure><img src="images/plantfamily.png" alt=""></figure>
                    </div>
                    <div class="back">
                        <p>Every plant within a plant family has numerous botanical traits in common. This is the highest classification group typically mentioned. In modern classification, a specific type of plant is designated to each family as an exemplar of that family's characteristics that set it apart from other families.</p>
                    </div>
                </div>
            </div>

            <div class="genus-frame">
                <div class="inner">
                    <div class="front">
                        <figure><img src="images/plantgenus.png" alt=""></figure>
                    </div>
                    <div class="back">
                        <p>This constitutes the aspect of plant nomenclature that is most commonly known. For instance, Actinodaphne is the genus for the Actionodaphne pilosa. Plants within a genus can be readily identified as belonging to the same group. The name of the genus should always be written with an initial capital letter.</p>
                    </div>
                </div>
            </div>

            <div class="species-frame">
                <div class="inner">
                    <div class="front">
                        <figure><img src="images/plantspecies.png" alt=""></figure>
                    </div>
                    <div class="back">
                        <p>This is the classification level that designates the individual plant. At this stage, certain aspects of the plant are defined more precisely—such as color, leaf shape, or the place where it was discovered or by whom. When the genus and species names are used together, they always refer to a single plant. The species name is placed after the genus and is never capitalized.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="plant-example">
        <h2>1 Family, 1 Genera, 1 Specie</h2>
        <div class="plant-card">

            <a href="Lauraceae.php">
                <div class="family-card">
                    <div class="card-front">
                        <p>Lauraceae</p>
                    </div>
                    <div class="card-back">
                        <p>Laurel consists of evergreen trees and shrubs. They has glossy leaves, tiny flowers and berry-like fruits. Suited to warm and damp climates, they thrives under forest coverings to avoid direct sunlight and keep a moist environment.</p>
                    </div>
                </div>
            </a>

            <a href="Actinodaphne.php">
                <div class="genus-card">
                    <div class="card-front">
                        <p>Actinodaphne</p>
                    </div>
                    <div class="card-back">
                        <p>This genus of dioecious evergreen trees and shrubs has 140 species in tropical and subtropical Asia. In China, there are 17 species, 13 of which are endemic. Trees are 3 to 25 meters tall. Leaves are clustered or nearly whorled. Flowers are star-shaped, small and green, unisexual. Fruit is a berry-like drupe on a perianth tube.</p>
                    </div>
                </div>
            </a>
            <a href="Actinodaphnepilosa.php">
                <div class="species-card">
                    <div class="card-front">
                        <p>Actinodaphne pilosa</p>
                    </div>
                    <div class="card-back">
                        <p>Pilose actinodaphne, an evergreen shrub or small tree related to bay laurel, grows in mixed forests and open shrublands below 500 meters. Its wood can be used for making glues for paper and hair.</p>
                    </div>
                </div>
            </a>
        </div>
        
    </div>
    
    

    <!-- Footer -->
    <?php include_once 'common/footer.inc'; ?>
</body>
</html>