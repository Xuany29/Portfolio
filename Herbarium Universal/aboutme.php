<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Partner - Profile Page</title>
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="styles/style.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lexend&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include_once 'common/header.inc'; ?>


    <!-- Header Section -->
    <header class="page-header">
        <h1>Our Partner</h1>
    </header>

    <!-- Profile Cards Container -->
    <div class="card-container">
        <!-- First Row -->
        <div class="card-row first-row">
            <!-- Card 1 -->
            <div class="card">
                <p class="name">Goldon Vun</p>
                <p class="description">Hi, I'm Goldon. Good to see you!</p>
                <a href="ProfileTable.php" class="more-btn">More</a>
            </div>
            <!-- Card 2 -->
            <div class="card">
                <p class="name">Yu Xi Phuan</p>
                <p class="description">Hi, I'm Yu Xi. Good to see you!</p>
                <a href="ProfileTable2.php" class="more-btn">More</a>
            </div>
            <!-- Card 3 -->
            <div class="card">
                <p class="name">Yu Xuan Wong</p>
                <p class="description">Hi, I'm Yu Xuan. Good to see you!</p>
                <a href="ProfileTable3.php" class="more-btn">More</a>
            </div>
        </div>
        <!-- Second Row -->
        <div class="card-row second-row">
            <!-- Card 4 -->
            <div class="card">
                <p class="name">Rahat Naz</p>
                <p class="description">Hi, I'm Rahat. Good to see you!</p>
                <a href="ProfileTable4.php" class="more-btn">More</a>
            </div>
            <!-- Card 5 -->
            <div class="card">
                <p class="name">Prudence Coredo</p>
                <p class="description">Hi, I'm Prudence. Good to see you!</p>
                <a href="ProfileTable1.php" class="more-btn">More</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include_once 'common/footer.inc'; ?>
</body>
</html>