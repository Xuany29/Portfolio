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
            <img src="./images/goldon.jpeg" alt="Profile Picture" class="profile-picture">
            </div>
        </section>
        <div class="name-id-course">
            <p>Goldon Vun Yik Yew</p>
            <h2>Student ID: 104400109</h2>
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
                        <li>Gender: Male</li>
                        <li>Birthdate: 31 January 2005</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td class="d1">Description of Hometown</td>
                <td class="d2">Kuching, located in Sarawak, Malaysia, is a charming city with a rich blend of cultures. The city is surrounded by lush rainforests and natural beauty, offering opportunities for nature lovers to explore. In Kuching, you can find some delicious local delicacies. Laksa Sarawak is a popular dish with a rich and flavourful broth. Kolo mee is another favourite, featuring thin noodles and savoury toppings. Emphasising the friendly locals and laid-back atmosphere, Kuching is a wonderful destination for travelors seeking an authentic Malaysian experience.
                </td>
            </tr>
            <tr>
                <td class="d1">A Great Achievement in My Life (So Far)</td>
                <td class="d2">As a St John Ambulance Sarawak adult member with a first aid certificate, this is truly a remarkable achievement in my life. It shows my dedication to helping others and being prepared to respond in times of emergencies. Having this certificate means that me have undergone extensive training and have acquired the skills and knowledge necessary to provide life-saving first aid.</td>
            </tr>
            <tr>
                <td class="d10">A List of My Favorite Books, Music, Movies, or Games</td>
                <td>
                    <ul>
                        <li>Favorite Book: "The Secret"</li>
                        <li>Favorite Music: "Vampirehollie"</li>
                        <li>Favorite Movie: "Spirited Away"</li>
                        <li>Favorite Game: "Monopoly Go"</li>
                    </ul>
                </td>
            </tr>
        </table>
        </div>
    </main>

    <div class="profile-button">
        <a href="mailto:104400109@students.swinburne.edu.my">Email Me</a>
    </div>

     <!-- Footer -->
     <?php include_once 'common/footer.inc'; ?>
   
    
</body>
</html>
