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
            <img src="./images/rahat.jpg" alt="Profile Picture" class="profile-picture">
            </div>
        </section>
        <div class="name-id-course">
            <p>Rahat Naz</p>
            <h2>Student ID: 104392837</h2>
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
                        <li>Age: 20</li>
                        <li>Gender: Female</li>
                        <li>Birthdate: 1 January 2004</li>
                        <li>Marital Status: Single</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="d1">Description of Hometown</td>
                <td class="d2">Lahore’s culture is a vibrant blend of history, tradition, and modernity. Known as the cultural heart of Pakistan, it is famous for its rich heritage, reflected in its Mughal architecture, such as the Badshahi Mosque and Lahore Fort, as well as its lively bazaars and street food. The city is a hub of arts, literature, music, and festivals, including Basant, the kite-flying festival. Lahore's residents are known for their hospitality and love of food, particularly Punjabi cuisine, making it a city full of warmth, color, and life.
            </td>
            </tr>
            <tr>
                <td class="d1">A Great Achievement in My Life (So Far)</td>
                <td class="d2">Since primary school to college, I have remained a standout student, getting one of the top three positions in primary and secondary grades but one great achievement that I feel is receiving 100% scholarship in my A Levels for academic excellence and maintaining it for 2 years.</td>
            </tr>
            <tr>
                <td class="d10">A List of My Favorite Books, Music, Movies, or Games</td>
                <td>
                    <ul>
                        <li>Favorite Book: "Harry Potter"</li>
                        <li>Favorite Music: "Punjabi"</li>
                        <li>Favorite Movie: "Enola Holmes"</li>
                        <li>Favorite Game: "Ludo"</li>
                    </ul>
                </td>
            </tr>
        </table>
        </div>
    </main>

    <div class="profile-button">
        <a href="mailto:104392837@students.swinburne.edu.my">Email Me</a>
    </div>

    <!-- Footer -->
    <?php include_once 'common/footer.inc'; ?>
   
    
</body>
</html>
