<?php
session_start();
require_once './database/database.php';

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
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
    SELECT b.BookingID, b.DepartureDate, r.Source, r.Destination, r.Cost, u.Username
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
    <link rel="stylesheet" href="./css/booking.css">
    <link rel="stylesheet" href="./css/mybooking.css">
    <link rel="stylesheet" href="./css/home.css">
    
</head>
<body>
    <div class="navbar">
        <div class="logo">Travel Express</div>
        <div class="book-ticket" style="margin-left: 150px;"><a href="deposit.php">Deposit</a></div>
        <div class="book-ticket" style="margin-left: 30px;"><a href="mybooking.php">My bookings</a></div>
        <div class="ml-auto luu" style="width:500px; display: flex; gap:25px; align-items:center;">
            <a href="homeloggedin.php" class="btn btn-outline-primary mr-2 btn-secondary">Home</a>
            <a href="#" class="btn btn-outline-primary mr-2 btn-secondary">Help</a></div>
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
                <p><span>Username:</span>  <?php echo htmlspecialchars($booking['Username']); ?></p>
                <p><span>Source:</span>  <?php echo htmlspecialchars($booking['Source']); ?></p>
                <p><span>Destination:</span>  <?php echo htmlspecialchars($booking['Destination']); ?></p>
                <p><span>Booking Date:</span>  <?php echo htmlspecialchars($booking['DepartureDate']); ?></p>               
                <p class="cost"><span>Cost:</span>  ETB <?php echo number_format($price, 2); ?></p>
                <p class="tax"><span>Tax:</span>  ETB <?php echo number_format($tax, 2); ?></p>
                <p class="total bb"><span>Total:</span>  ETB <?php echo number_format($cost, 2); ?></p>
                <?php if ($isExpired): ?>
                    <p class="days-left bb">Status: Expired</p>
                <?php else: ?>
                    <p class="days-left bb">Days Left: <?php echo $daysLeft; ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
