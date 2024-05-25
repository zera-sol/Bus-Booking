<?php
session_start();
// Take username, email, phone from the session and store them in variables sent from login.php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
require_once './database/database.php';

$error = '';
$success = '';

$username = $_SESSION['username'];
$email = $_SESSION['email'];
$phone = $_SESSION['phone'];
// Take username's first two letters, capitalize them, and store them in a variable called $initials
$initials = strtoupper(substr($username, 0, 2));

// Create an instance of the Database class
$database = new Database();
$conn = $database->conn;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $source = $_POST['source'];
    $destination = $_POST['destination'];
    $departureDate = $_POST['departure_date'];

    // Check if there is a route from the source to destination in the Database Route table
    $query = "SELECT * FROM routes WHERE Source = :source AND Destination = :destination";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':source', $source);
    $stmt->bindParam(':destination', $destination);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Route exists, store the RouteID in the Booking table
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $routeID = $row['RouteID'];

        // Check if the user exists in the Users table
        $query = "SELECT * FROM users WHERE Username = :username";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // User exists, store the UserID in the Booking table
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $userID = $row['UserID'];

            // Store the booking details in the Booking table
            $query = "INSERT INTO bookings (UserID, RouteID, PaymentStatus, DepartureDate) VALUES (:userID, :routeID, 'Pending', :departureDate)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':userID', $userID);
            $stmt->bindParam(':routeID', $routeID);
            $stmt->bindParam(':departureDate', $departureDate);

            if ($stmt->execute()) {
                $success = "Booking successful!";
            } else {
                $error = "Failed to book the trip.";
            }
        } else {
            $error = "User not found.";
        }
    } else {
        $error = "No route found from the source to destination.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Express</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/booking.css">
</head>
<body>
<div class="navbar">
    <div class="logo">Travel Express</div>
    <div class="nav-links">
        <a href="#">Draft</a>
        <a href="#">My Booking</a>
        <a href="#">Help</a>
        <a href="#footer" id="contact">Contact Us</a>
    </div>
    <div class="user-profile" id="show-profile">
        <?php echo htmlspecialchars($initials); ?>
    </div>
    <div class="profile-page">
        <button id="close-btn">X</button>
        <ul>
            <li><a href="Profile.html">Profile</a></li>
            <li><a href="#footer" id="contact2">Contact Us</a></li>
            <li><a href="#">Help</a></li>
            <li><a href="#">My Bookings</a></li>
            <li><a href="#">Logout</a></li>
        </ul>
    </div>
</div>

<div class="content">
    <h1>Book a trip</h1>
    <div class="trip-form">
        <form action="booking.php" method="POST" >
            <label for="user-name" class="label">User name:</label>
            <input type="text" name="user-name" value="<?php echo htmlspecialchars($username); ?>" disabled>
            <label for="email" class="label">Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" disabled>
            <label for="phone" class="label">Phone:</label>
            <input type="number" name="phone-number" value="<?php echo htmlspecialchars($phone); ?>" disabled>
            <div id="search-trip">
                <div>
                    <label for="source" class="label">From:</label>
                    <select name="source" required>
                        <option value="0">Select Source</option>
                        <option value="Addis Ababa">Addis Ababa</option>
                        <option value="Hossana">Hossana</option>
                        <option value="Bahirdar">Bahirdar</option>
                        <option value="Gonder">Gonder</option>
                        <option value="Arbaminch">Arbaminch</option>
                        <option value="Diredwa">Diredwa</option>
                    </select>
                </div>
                <div>
                    <label for="destination" class="label">To:</label>
                    <select name="destination" required>
                        <option value="0">Select Destination</option>
                        <option value="Addis Ababa">Addis Ababa</option>
                        <option value="Hossana">Hossana</option>
                        <option value="Bahirdar">Bahirdar</option>
                        <option value="Gonder">Gonder</option>
                        <option value="Arbaminch">Arbaminch</option>
                        <option value="Diredwa">Diredwa</option>
                    </select>
                </div>
            </div>
            <label for="departure_date" class="label">Departure Date:</label>
            <input type="date" name="departure_date" required>
            <p id="paragraph-warning">Warning: <span>If you are not able to pay the cost in 6 hours, your booking status will be canceled automatically.</span></p>
            <?php if ($error): ?>
                <p style="background: #F2DEDE; color: #A94442; padding: 10px; width: 500px;
                    border-radius: 5px; margin: 5px auto;"><?php echo $error; ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                <p style="background: #bfeccb; color: #38a66f; padding: 10px; width: 500px;
                    border-radius: 5px; margin: 5px auto;"><?php echo $success; ?></p>
                <?php endif; ?>
                <!-- make the above two P tags be desplayed only for 10 seconds -->
                <?php if ($error || $success): ?>
                    <script>
                        setTimeout(function() {
                            document.querySelectorAll('.notification').forEach(function(notification) {
                                notification.style.display = 'none';
                            });
                        }, 10000);
                    </script>
                <?php endif; ?>
        <button type="submit">Book now</button>
        </form>
    </div>
    <div class="destinations">
        <h2>Popular destinations</h2>
        <div class="destination-grid">
            <div class="destination">
                <img src="./images/aa.jpg" alt="Addis Ababa">
                <p>Addis Ababa</p>
            </div>
            <div class="destination">
                <img src="./images/bahirdar.jpg" alt="Bahirdar">
                <p>Bahirdar</p>
            </div>
            <div class="destination">
                <img src="./images/arbaminch.jpg" alt="Arbaminch">
                <p>Arbaminch</p>
            </div>
            <div class="destination">
                <img src="./images/gonder.jpg" alt="Gonder">
                <p>Gonder</p>
            </div>
            <div class="destination">
                <img src="./images/diredwa.jpg" alt="Diredwa">
                <p>Diredwa</p>
            </div>
            <div class="destination">
                <img src="./images/hossana.jpg" alt="Hossana">
                <p>Hossana</p>
            </div>
        </div>
    </div>
    <div class="footer" id="footer">
        <div class="contact">
            <h3>Contact Us</h3>
            <p>Phone: +251-911-111-111</p>
            <p>Email: traveExpreSs@gmail.org</p>
            <p>Address: Addis Ababa, Ethiopia</p>
            <p>&copy;2021 Travel Express. All rights reserved</p>
        </div>
        <div>
            <h3>Follow Us</h3>
            <p>Facebook</p>
            <p>Twitter</p>
            <p>Instagram</p>
            <p>LinkedIn</p>
        </div>
        <div>
            <h3>Quick Links</h3>
            <p>Home</p>
            <p>Book a trip</p>
            <p>My Booking</p>
            <p>Help</p>
        </div>
    </div>
</div>

<script>
    // function checkRoute(event) {
    //     const source = document.querySelector('select[name="source"]').value;
    //     const destination = document.querySelector('select[name="destination"]').value;

    //     if (source === '0' || destination === '0') {
    //         alert('Please select both source and destination.');
    //         event.preventDefault();
    //         return false;
    //     } else if (source === destination) {
    //         alert('Source and destination cannot be the same.');
    //         event.preventDefault();
    //         return false;
    //     } else if ((source !== 'Addis Ababa' && destination !== 'Addis Ababa') || (source !== 'Addis Ababa' && destination === 'Addis Ababa')) {
    //         alert('There is no route from the source to the destination.');
    //         event.preventDefault();
    //         return false;
    //     }
    //     return true;
    // }

    const showProfile = document.getElementById('show-profile');
    const profilePage = document.querySelector('.profile-page');
    const closeBtn = document.getElementById('close-btn');

    showProfile.addEventListener('click', () => {
        profilePage.classList.toggle('show');
    });

    closeBtn.addEventListener('click', () => {
        profilePage.classList.remove('show');
    });

    window.addEventListener('click', (e) => {
        if (e.target !== showProfile && !showProfile.contains(e.target) && e.target !== profilePage && !profilePage.contains(e.target)) {
            profilePage.classList.remove('show');
        }
    });

    document.querySelector('#contact').addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector('#footer').scrollIntoView({ behavior: 'smooth' });
    });
</script>
</body>
</html>
