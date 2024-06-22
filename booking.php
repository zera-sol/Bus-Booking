<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
else if($_SESSION['role'] != 'user'){
    header("Location: login.php");
    exit();
}

require_once './database/database.php';

$error = '';
$success = '';

$id = $_SESSION['id'];

$current_time = date("Y-m-d H:i:s");

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
$Name = $user["Name"];

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
            $query = "INSERT INTO bookings (UserID, RouteID, PaymentStatus, DepartureDate, Time) VALUES (:userID, :routeID, 'Pending', :departureDate, :current_time)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':userID', $id);
            $stmt->bindParam(':routeID', $routeID);
            $stmt->bindParam(':departureDate', $departureDate);
            $stmt->bindParam(':current_time', $current_time);

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
    <title>Travel Express</title>
    <link rel="stylesheet" href="./css/navbar.css">
    <link rel="stylesheet" href="./css/booking.css">
    
</head>
<body>
      <!-- Nav bar -->
<div class="navbar" style="display:flex;justify-content:center; gap:10px;">
        <div class="logo" style="font-weight: bold; font-size: 1.5rem; width:300px;">Travel Express</div>
        <div class="laa" style="margin-left: 110px; padding: 5px; border-radius: 5px;"><a href="deposit.php" style="text-decoration: none;">Deposit</a></div>
        <div class="laa" style="margin-left: 30px; padding: 5px; border-radius: 5px;"><a href="draft.php" style="text-decoration: none;">Draft</a></div>
        <div class="laa" style="margin-left: 30px; padding: 5px; border-radius: 5px;"><a href="mybooking.php" style="text-decoration: none;">Tickets</a></div>
        <div class="luu" style="width:500px; display: flex; gap:35px; align-items:center; margin-left: 400px;">
            <a href="edit-user.php" class="not-logout">Profile</a>
            <a href="homeloggedin.php" class="not-logout">Home</a>            
            <a href="home.php" style=" background-color: rgb(76, 76, 76); color: white;">Logout</a>
            <div style="border-radius: 50%; padding: 10px; background-color:rgb(0, 0, 226); color:white; font-weight:bold;"><?php echo htmlspecialchars($initials); ?></div>
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
                    <input type="text" name="user-name" value="<?php echo htmlspecialchars($Name); ?>" disabled>
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
 <!-- Footer section of a user -->
 <footer class="container footer-section" id="footer">
        <div class="row">
            <div class="row-box">
                <div class="footer-title">About Us</div>
                <ul class="list-unstyled">
                    <li><a href="#">Company Information</a></li>
                    <li><a href="#">Career Opportunities</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms & Conditions</a></li>
                </ul>
            </div>
            <div class="row-box">
                <div class="footer-title">Support</div>
                <ul class="list-unstyled">
                    <li><a href="#">Customer Service</a></li>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Report an Issue</a></li>
                    <li><a href="#">Travel Alerts</a></li>
                </ul>
            </div>
            <div class="row-box">
                <div class="footer-title">Contact Us</div>
                <ul class="list-unstyled">
                    <li><a href="#">Email Us</a></li>
                    <li><a href="#">Call Us</a></li>
                    <li><a href="#">Follow Us</a></li>
                    <li><a href="#">Locations</a></li>
                </ul>
            </div>
        </div>
        <div class="bottom-text">
            &copy; 2023 Travel Express. All rights reserved.
        </div>
    </footer>
            
        </body>
        </html>
