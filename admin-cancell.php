<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["id"];
require_once './database/database.php';

// Create an instance of the Database class
$database = new Database();
$conn = $database->conn;

$stmt = $conn->prepare("SELECT * FROM Users WHERE UserID = :userid");
$stmt->bindParam(':userid', $id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user['Username'];

// Take username's first two letters, capitalize them, and store them in a variable called $initials
$initials = strtoupper(substr($username, 0, 2));

// Initialize variables
$searchResults = [];
$deleteMessage = '';

// Handle the delete functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $bookingID = isset($_POST['bookingID']) ? $_POST['bookingID'] : '';
    $paymentStatus = isset($_POST['paymentStatus']) ? $_POST['paymentStatus'] : '';
    $departureDate = isset($_POST['departureDate']) ? $_POST['departureDate'] : '';
    $userID = isset($_POST['userID']) ? $_POST['userID'] : '';
    $routeCost = isset($_POST['routeCost']) ? $_POST['routeCost'] : '';
    $booked_date = isset($_POST['Time']) ? $_POST['Time'] : '';

    try {
        // Get current date and time
        $currentDate = new DateTime();
        // Create DateTime object for booked date and time
        $departureDateTime = new DateTime($booked_date);

        // Calculate the interval in hours
        $interval = ($currentDate->getTimestamp() - $departureDateTime->getTimestamp()) / 3600;

        if ($paymentStatus === 'Pending' && $interval >= 6) {
            // Delete the booking
            $stmt = $conn->prepare("DELETE FROM Bookings WHERE BookingID = :bookingID");
            $stmt->bindParam(':bookingID', $bookingID);
            $stmt->execute();
            $deleteMessage = 'Booking deleted successfully (pending status).';
        } elseif ($paymentStatus === 'cancelled') {
            // Delete the booking and update user's deposit
            $stmt = $conn->prepare("DELETE FROM Bookings WHERE BookingID = :bookingID");
            $stmt->bindParam(':bookingID', $bookingID);
            $stmt->execute();

            $depositAmount = $routeCost * 0.5;
            $stmt = $conn->prepare("UPDATE Users SET Deposit = Deposit + :depositAmount WHERE UserID = :userID");
            $stmt->bindParam(':depositAmount', $depositAmount);
            $stmt->bindParam(':userID', $userID);
            $stmt->execute();
            $deleteMessage = 'Booking deleted successfully (cancelled status). 50% of the route cost has been refunded.';
        } else {
            $deleteMessage = 'Error deleting booking: Invalid conditions.';
        }
    } catch (PDOException $e) {
        $deleteMessage = 'Error deleting booking: ' . $e->getMessage();
    }
}
// Fetch all bookings with the specified parameters
try {
    $query = "SELECT b.BookingID, b.SeatNumber, b.PaymentStatus, b.DepartureDate, b.Time, u.Username, r.Destination, r.Source, r.Cost, u.UserID
              FROM Bookings b 
              JOIN Users u ON b.UserID = u.UserID 
              JOIN Routes r ON b.RouteID = r.RouteID 
              WHERE b.PaymentStatus IN ('pending', 'cancelled')";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $searchError = 'Error fetching bookings: ' . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Express</title>
    <link rel="stylesheet" href="./css/checkBooking-1.css">
</head>
<style>
    #message{
        background-color: #4CAF50;
        color: white;
        text-align: center;
        padding: 10px;
        margin-bottom: 10px;
    
    }
</style>
<script>
        // Hide message after 5 seconds
        function hideMessage() {
            setTimeout(function() {
                var messageElement = document.getElementById('message');
                if (messageElement) {
                    messageElement.style.display = 'none';
                }
            }, 5000); // 5000 milliseconds = 5 seconds
        }

        window.onload = hideMessage;
    </script>
<body>
<header>
    <div class="navBar">
        <div class="logo">Travel Express Admin</div>
        <nav>
            <a href="admin-home.php">Dashboard</a>
            <a href="admin-bookings.php">Check Bookings</a>
            <a href="admin-verify.php">Verify Deposit</a>
            <a href="admin-cancell.php">Cancel ticket</a>
            <a href="admin-routes.php">Manage Route</a>
            <a href="logout.php">Logout</a>
            <div class="profile-pic"><?php echo htmlspecialchars($initials); ?></div>
        </nav>
    </div>        
</header>
<main>
    <div class="container">
         <?php if ($deleteMessage): ?>
                <p class="message" id="message"><?php echo htmlspecialchars($deleteMessage); ?></p>
            <?php endif; ?>
        <div class="table-container">
            <?php if (isset($searchError) && $searchError): ?>
                <p class="error"><?php echo htmlspecialchars($searchError); ?></p>
            <?php elseif (empty($searchResults)): ?>
                <p>No bookings found</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr class="table-head">
                            <th>Booking ID</th>
                            <th>Booking Date</th>
                            <th>Username</th>
                            <th>Departure Location</th>
                            <th>Destination Address</th>
                            <th>Booked Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($searchResults as $result): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($result['BookingID']); ?></td>
                                <td><?php echo htmlspecialchars($result['DepartureDate']); ?></td>
                                <td><?php echo htmlspecialchars($result['Username']); ?></td>
                                <td><?php echo htmlspecialchars($result['Source']); ?></td>
                                <td><?php echo htmlspecialchars($result['Destination']); ?></td>
                                <td><?php echo htmlspecialchars($result['Time']); ?></td>
                                <td><span class="status <?php echo strtolower(str_replace(' ', '-', htmlspecialchars($result['PaymentStatus']))); ?>">
                                    <?php echo htmlspecialchars($result['PaymentStatus']); ?></span>
                                </td>
                                <td>
                                    <form method="POST" action="">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="bookingID" value="<?php echo htmlspecialchars($result['BookingID']); ?>">
                                        <input type="hidden" name="paymentStatus" value="<?php echo htmlspecialchars($result['PaymentStatus']); ?>">
                                        <input type="hidden" name="departureDate" value="<?php echo htmlspecialchars($result['DepartureDate']); ?>">
                                        <input type="hidden" name="userID" value="<?php echo htmlspecialchars($result['UserID']); ?>">
                                        <input type="hidden" name="routeCost" value="<?php echo htmlspecialchars($result['Cost']); ?>">
                                        <input type="hidden" name="Time" value="<?php echo htmlspecialchars($result['Time']); ?>">
                                        <button type="submit" style="width:100%">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html> 
