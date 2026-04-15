<!DOCTYPE html>
<html lang="en">
<head>
    <title>Personal Profile</title>
    <meta charset="UTF-8">
    <!-- Link to external CSS -->
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <!-- Header -->
    <?php include_once 'common/header.inc'; ?>

    <!--ProfilePage-->
    <main>
        <section class="profile">
            <div class="image">
            <img src="./images/Prudencia.png" alt="Profile Picture" class="profile-picture">
            </div>
        </section>
        <div class="name-id-course">
            <p>Prudence Coredo</p>
            <h2>Student ID: 104396729</h2>
            <h2>Bachelor of Computer Science</h2>
        </div>
        <div class="table">
        <table class="profile-table">
            <tr>
                <th class="h1">Attribute</th>
                <th>Information</th>
            </tr>
            <tr>
                <td class="d1">Demographic Information About Me</td>
                <td class="d2">               
                    <ul>
                        <li>Age: 19</li>
                        <li>Gender: Female</li>
                        <li>Birthdate: 8 August 2005</li>
                        <li>Marital Status: Single</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="d1">Description of Hometown</td>
                <td class="d2">Kenya is a country rich in both natural beauty and cultural diversity. Its vast savannahs are famous for hosting the "Big Five"—lion, leopard, elephant, buffalo, and rhino—making it a top destination for wildlife enthusiasts. Fun fact:  Kenya's capital,  Nairobi  is the only capital city in the world with a national park within the city.Culturally, Kenya is a melting pot with over 60 languages spoken, reflecting its multi-ethnic population. The vibrant and diverse communities contribute to a lively atmosphere across the country. Whether exploring the urban hustle of Nairobi or the quiet serenity of the wilderness, Kenya offers an incredible blend of adventure, history, and tradition.</td>
            </tr>
            <tr>
                <td class="d1">A Great Achievement in My Life (So Far)</td>
                <td class="d2">Contributed to a community initiative aimed at enhancing the living conditions of children with sight impairments. The project focused on providing essential resources such as specialized educational materials, assistive technologies, and recreational activities designed to support their development and integration.</td>
            </tr>
            <tr>
                <td class="d10">A List of My Favorite Books, Music, Movies, or Games</td>
                <td>
                    <ul>
                        <li>Favorite Book: "Americanah"</li>
                        <li>Favorite Music: "Oh My Mama"</li>
                        <li>Favorite Movie: "The Hate You Give"</li>
                        <li>Favorite Game: "Homescapes"</li>
                    </ul>
                </td>
            </tr>
        </table>
        </div>
    </main>

    <div class="profile-button">
        <a href="mailto:104396729@students.swinburne.edu.my">Email Me</a>
    </div>

    <!-- Footer -->
    <?php include_once 'common/footer.inc'; ?>
   
    
</body>
</html>
