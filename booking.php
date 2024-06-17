<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

require_once './database/database.php';

$error = '';
$success = '';

$id = $_SESSION['id'];

// Create an instance of the Database class
$database = new Database();
$conn = $database->conn;

$stmt = $conn->prepare("SELECT * FROM Users WHERE UserID = :userid");
$stmt->bindParam(':userid', $id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user['Username'];
$email = $user["Email"];
$phone = $user["Phone"];
$deposit = $user["Deposit"];

// Take username's first two letters, capitalize them, and store them in a variable called $initials
$initials = strtoupper(substr($username, 0, 2));

$source = $destination = $departureDate = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $source = $_POST['source'];
    $destination = $_POST['destination'];
    $departureDate = $_POST['departure_date'];

    // Check if departure date is valid (at least tomorrow)
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    if ($departureDate < $tomorrow) {
        $error = "Invalid departure date. Please select a date starting from tomorrow.";
    } else {
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

            // Store the booking details in the Booking table
            $query = "INSERT INTO bookings (UserID, RouteID, PaymentStatus, DepartureDate) VALUES (:userID, :routeID, 'Pending', :departureDate)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':userID', $id);
            $stmt->bindParam(':routeID', $routeID);
            $stmt->bindParam(':departureDate', $departureDate);

            if ($stmt->execute()) {
                $success = "Booking successful!";
                // Retrieve the last inserted booking ID
                $bookingID = $conn->lastInsertId();
                // Store the booking ID in the session
                $_SESSION['booking_id'] = $bookingID;
                // Redirect to the payment.php page
                header("Location: payment.php");
                exit();
            } else {
                $error = "Failed to book the trip.";
            }
        } else {
            $error = "No route found from the source to destination.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BusGo</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/home.css">
    <link rel="stylesheet" href="./css/booking.css">
    
</head>
<body>
    <div class="navbar">
        <div class="logo">Travel Express</div>
        <div class="book-ticket" style="margin-left: 150px;"><a href="deposit.php">Deposit</a></div>
        <div class="book-ticket" style="margin-left: 30px;"><a href="mybooking.php">My bookings</a></div>
        <div class="ml-auto luu" style="width:500px; display: flex; gap:25px; align-items:center;">
            <a href="homeloggedin.php" class="btn btn-outline-primary mr-2 btn-secondary">Home</a>
            <a href="#" class="btn btn-outline-primary mr-2 btn-secondary">Help</a>
            <a href="#footer" class="btn btn-outline-primary mr-2 btn-secondary">Contact</a> 
            <div style="border-radius: 50%; padding: 10px; background-color:blue; color:white; font-weight:bold;"> <?php echo htmlspecialchars($initials); ?></div>
            <div id="balance" style=" color: green; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif; font-weight: bold;"> ETB <?php echo htmlspecialchars($deposit); ?></div>
        </div>
    </div>
    <hr/>

        <div class="content">
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
