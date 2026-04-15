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
            <img src="./images/mckinley.jpg" alt="Profile Picture" class="profile-picture">
            </div>
        </section>
        <div class="name-id-course">
            <p>Yu Xi Phuan</p>
            <h2>Student ID: 104383093</h2>
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
                        <li>Birthdate: 4 April 2005</li>
                        <li>Marital Status: Single</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="d1">Description of Hometown</td>
                <td class="d2">Johor Bahru is a coastal town of Johor state in Malaysia. Over sixty per cent of the residents are Malaysians, but there is a mix of Malay, Chinese, and Indians. As for fresh ocean seafood you can try the famous here Laksa Johor, Mee Rebus and Otak-Otak. Owing to the fact that Johor Bahru is sandwiched between beaches and greens, a similar experience in exploring the natural world involves visiting sea side facilities, walks in parks, as well as forest trails.</td>
            </tr>
            <tr>
                <td class="d1">A Great Achievement in My Life (So Far)</td>
                <td class="d2">Deployed as a school monitor in secondary school and campaigned for order by successfully facilitating class order. Doing this job, I became more responsible and serious while working with the conflicts, which helped me to create succesful leadership and decision-making skills.</td>
            </tr>
            <tr>
                <td class="d10">A List of My Favorite Books, Music, Movies, or Games</td>
                <td>
                    <ul>
                        <li>Favorite Book: "The Girl With No Name"</li>
                        <li>Favorite Music: "Moonlit Dream"</li>
                        <li>Favorite Movie: "Jack Pot"</li>
                        <li>Favorite Game: "Roblox"</li>
                    </ul>
                </td>
            </tr>
        </table>
        </div>
    </main>

    <div class="profile-button">
        <a href="mailto:104383093@students.swinburne.edu.my">Email Me</a>
    </div>

    <!-- Footer -->
    <?php include_once 'common/footer.inc'; ?>
   
    
</body>
</html>
