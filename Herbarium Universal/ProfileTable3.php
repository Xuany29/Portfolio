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
            <img src="./images/yuxuan.jpeg" alt="Profile Picture" class="profile-picture">
            </div>
        </section>
        <div class="name-id-course">
            <p>Yu Xuan Wong</p>
            <h2>Student ID: 104392549</h2>
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
                        <li>Birthdate: 29 October 2005</li>
                        <li>Marital Status: Single</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="d1">Description of Hometown</td>
                <td class="d2">Sibu is a landlocked city located in Sarawak, Malaysia. Most of the residents are Chinese descent, primarily from the Fuzhou region. Sibu has a variety of delicious dishes especially food inspired from Fuzhou such as Kampua, Kompia and Dian Mian Hu. Because of Sibu has many rainforests, residents and tourists also like to explore natural environment through climbing mountains, jogging in forest park.
                </td>
            </tr>
            <tr>
                <td class="d1">A Great Achievement in My Life (So Far)</td>
                <td class="d2">Became a badminton school team member when secondary school and achieved second runner-up in interschool competition. During the process of getting this achievement, I learnt to be more perseverant and calmer while facing difficulties.
                </td>
            </tr>
            <tr>
                <td class="d10">A List of My Favorite Books, Music, Movies, or Games</td>
                <td>
                    <ul>
                        <li>Favorite Book: "The Miracles of the Namiya General Store"</li>
                        <li>Favorite Music: "Stuck in the Middle"</li>
                        <li>Favorite Movie: "Harry Potter"</li>
                        <li>Favorite Game: "Sun Haven"</li>
                    </ul>
                </td>
            </tr>
        </table>
        </div>
    </main>

    <div class="profile-button">
        <a href="mailto:104392549@students.swinburne.edu.my">Email Me</a>
    </div>

    <!-- Footer -->
    <?php include_once 'common/footer.inc'; ?>
   
    
</body>
</html>
