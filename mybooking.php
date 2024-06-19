<?php
session_start();
require_once './database/database.php';

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
else if($_SESSION['role'] != 'user'){
    header("Location: login.php");
    exit();
}

// Retrieve the username from the session
$id = $_SESSION['id'];

// Create an instance of the Database class
$database = new Database();
$conn = $database->conn;

// Retrieve all bookings for the logged-in user with paymentStatus = 'paid'
$query = "
    SELECT b.BookingID, b.DepartureDate, b.SeatNumber, r.Source, r.Destination, r.Cost, u.Name
    FROM bookings b
    JOIN routes r ON b.RouteID = r.RouteID
    JOIN Users u ON b.UserID = u.UserID
    WHERE b.UserID = :userID AND b.PaymentStatus = 'paid'
    ORDER BY b.BookingID DESC
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':userID', $id);
$stmt->execute();

$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

function calculateDaysLeft($departureDate) {
    $currentDate = new DateTime();
    $departureDate = new DateTime($departureDate);
    $interval = $currentDate->diff($departureDate);
    return $interval->format('%r%a'); // Returns the difference in days with a sign
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
    <link rel="stylesheet" href="./css/navbar.css">
    <link rel="stylesheet" href="./css/mybooking.css">
</head>
<body>
      <!-- NavBars of a User -->
      <div class="navbar">
        <div class="logo" style="font-weight: bold; font-size: 1.5rem;">Travel Express</div>
        <div class="laa" style="margin-left: 120px; padding: 5px; border-radius: 5px;"><a href="deposit.php" style="text-decoration: none;">Deposit</a></div>
        <div class="laa" style="margin-left: 30px; padding: 5px; border-radius: 5px;"><a href="mybooking.php" style="text-decoration: none;">My bookings</a></div>
        <div class="luu" style="width:500px; display: flex; gap:35px; align-items:center; margin-left: 250px;">
            <a href="edit-user.php" class="not-logout">Profile</a>
            <a href="#footer" class="not-logout">Contact</a>            
            <a href="home.php" style=" background-color: rgb(76, 76, 76); color: white;">Logout</a>
            <div style="border-radius: 50%; padding: 10px; background-color:rgb(0, 0, 226); color:white; font-weight:bold;">ZH</div>
            <div id="balance" style=" color: green; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif; font-weight: bold;"> ETB 45000</div>
        </div>
    </div>
    <hr/>


<div class="content">
    <h1>Your Bookings</h1>
    <?php if (empty($bookings)): ?>
        <p>No bookings found.</p>
    <?php else: ?>
        <?php foreach ($bookings as $booking): ?>
            <?php
            $daysLeft = calculateDaysLeft($booking['DepartureDate']);
            $isExpired = $daysLeft < 0;
            $cost = $booking['Cost'];
            $tax = $cost * 0.15;
            $price = $cost - $tax;
            ?>
            <div class="ticket <?php echo $isExpired ? 'expired' : ''; ?>">
                <h2><span>Booking ID:</span> <?php echo htmlspecialchars($booking['BookingID']); ?></h2>                
                <div class="dis-flx">
                    <div>
                        <p> <?php echo htmlspecialchars($booking['Name']); ?></p>
                        <p><span>From:</span> <?php echo htmlspecialchars($booking['Source']); ?></p>
                        <p><span>Seat:</span> <?php echo htmlspecialchars($booking['SeatNumber']); ?></p>             
                        <p class="cost"><span>Cost:</span>  ETB <?php echo number_format($price, 2); ?></p>
                    </div>
                    <div>
                        <p> <?php echo htmlspecialchars($booking['DepartureDate']); ?></p>                        
                        <p><span>To:</span> <?php echo htmlspecialchars($booking['Destination']); ?></p> 
                        <p class="tax"><span>Tax:</span>  ETB <?php echo number_format($tax, 2); ?></p>
                        <p class="total bb"><span>Total:</span>  ETB <?php echo number_format($cost, 2); ?></p>
                    </div>
                        <!-- <?php if ($isExpired): ?>
                            <p class="days-left bb">Status: Expired</p>
                        <?php else: ?>
                            <p class="days-left bb">Days Left: <?php echo $daysLeft; ?></p>
                        <?php endif; ?> -->
                </div>
                <button>Cancel Ticket</button>
                <h6>HAVE A WONDERFULL TRIP</h6>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
  
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
