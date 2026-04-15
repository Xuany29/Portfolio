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

    <!-- Top Searchbar -->
    <div class="searchbar">
        <figure><img src="./images/plants.png" alt=""></figure>
        <div class="searchbar-header">
            <h1>Search a Plants</h1>
            <h2>Discover the 500+ species family, distributed across 17 genera.</h2>
        </div>
       
        <div class="search-nav">
            <input type="text" name="searchplants" placeholder="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Enter common name or scientific name here...">
        </div>
        <figure class="search-interface-symbol"><img src="./images/search-interface-symbol.png" alt=""></figure>
    </div>

    <!-- Plants Swiper -->
    <div class="container">
        <div class="main-swiper">
            <div class="slides">
                <div class="slide">
                    <div class="main-content clearfix">
                        <div class="slide-img">
                            <figure><img src="./images/lauraceae.jpeg" alt=""></figure>
                        </div>
                        <div class="slide-detail leftfix">
                            <div class="slide-main-details">
                                <h1><em>Lauraceae</em></h1>
                                <h2>樟科</h2>
                            </div>
                            <div class="slide-sub-detail">
                                <p>Angiosperms(Dicotyledons)</p>
                                <div class="more-detail-button">
                                    <a href="Lauraceae.php">More &gt;</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="slide">
                    <div class="main-content clearfix">
                        <div class="slide-img">
                            <figure><img src="./images/actinodaphne.jpeg.webp" alt=""></figure>
                        </div>
                        <div class="slide-detail leftfix">
                            <div class="slide-main-details">
                                <h1><em>Actinodaphne</em></h1>
                                <h2>黄肉楠属</h2>
                            </div>
                            <div class="slide-sub-detail">
                                <p>Angiosperms(Dicotyledons)</p>
                                <div class="more-detail-button">
                                    <a href="Actinodaphne.php">More &gt;</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="slide">
                    <div class="main-content clearfix">
                        <div class="slide-img">
                            <figure><img src="./images/actinodaphnepilosa.jpeg.webp" alt=""></figure>
                        </div>
                        <div class="slide-detail leftfix">
                            <div class="slide-main-details">
                                <h1><em>Actinodaphne pilosa</em></h1>
                                <h2>毛黃肉楠</h2>
                            </div>
                            <div class="slide-sub-detail">
                                <p>Angiosperms(Dicotyledons)</p>
                                <div class="more-detail-button">
                                    <a href="Actinodaphnepilosa.php">More &gt;</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="slide">
                    <div class="main-content clearfix">
                        <div class="slide-img">
                            <figure><img src="./images/blackcherry.webp" alt=""></figure>
                        </div>
                        <div class="slide-detail leftfix">
                            <div class="slide-main-details">
                                <h1><em>Black Cherry</em></h1>
                                <h2>晚花稠李（野黑櫻）</h2>
                            </div>
                            <div class="slide-sub-detail">
                                <p>Angiosperms(Dicotyledons)</p>
                                <div class="more-detail-button">
                                    <a href="blackcherry.php">More &gt;</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Team Work Table Area -->
    <section class="news-container">
        <div class="content-wrapper">
            <div class="news-header">
                <h1>Announcements</h1>
            </div>
            <div class="table-and-image">
                <div class="table-wrapper">
                    <table class="news-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Titles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="news-row">
                                <td class="news-date">22/09/2024</td>
                                <td class="news-content">
                                    <a href="classify.php">“Plant Classification: Unraveling the Mysteries of Family, Genus, and Species”</a>
                                </td>
                            </tr>
                            <tr class="news-row">
                                <td class="news-date">23/09/2024</td>
                                <td class="news-content">
                                    <a href="tutorial.php">“Make Your Own Herbarium: A Fascinating Journey into the World of Plants”</a>
                                </td>
                            </tr>
                            <tr class="news-row">
                                <td class="news-date">25/09/2024</td>
                                <td class="news-content">
                                    <a href="contribute.php">“Show Us Your First Plant Discovery: Unveiling the Hidden World of Flora”</a>
                                </td>
                            </tr>
                            <tr class="news-row">
                                <td class="news-date">28/09/2024</td>
                                <td class="news-content">
                                    <a href="identify.php">"Picture and look for us: A professional botanist with you"</a>
                                </td>
                            </tr>                
                        </tbody>
                    </table>
                </div>

                <div class="news-image-container">
                    <div class="image-wrapper">
                        <a href="diyspecimen.php">
                            <figure>
                                <img src="./images/Herbarium.jpg" alt="">
                            </figure>
                        </a>
                    </div>
                </div>
        
                <a href="diyspecimen.php" class="circle-btn">
                    &gt;
                </a>
            </div>
        </div>
    </section>
    

    
    <!-- Footer -->
    <?php include_once 'common/footer.inc'; ?>
</body>
</html>