<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

require_once './database/database.php';

$id = $_SESSION['id'];

// Create an instance of the Database class
$database = new Database();
$conn = $database->conn;

// Query to count total number of users
$query = "SELECT COUNT(*) as total_users FROM Users";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalUsers = $result['total_users'];

// Query to count total number of paid bookings
$query = "SELECT COUNT(*) as total_paid_bookings FROM bookings WHERE paymentStatus = 'paid'";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalPaidBookings = $result['total_paid_bookings'];

// Query to count total number of buses
$query = "SELECT COUNT(*) as total_buses FROM Buses";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalBuses = $result['total_buses'];

// Query to count total number of pending bookings
$query = "SELECT COUNT(*) as total_pending_bookings FROM bookings WHERE paymentStatus = 'pending'";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$totalPendingBookings = $result['total_pending_bookings'];

// Get today's, tomorrow's, and yesterday's dates
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$yesterday = date('Y-m-d', strtotime('-1 day'));
$two_days_ago = date('Y-m-d', strtotime('-2 days'));

// Fetch current bookings (today)
$query = "SELECT b.BookingID, u.Username, r.Source, r.Destination, b.DepartureDate, b.paymentStatus 
          FROM bookings b 
          JOIN Users u ON b.UserID = u.UserID 
          JOIN routes r ON b.RouteID = r.RouteID 
          WHERE b.DepartureDate = :today";
$stmt = $conn->prepare($query);
$stmt->bindParam(':today', $today);
$stmt->execute();
$currentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch past bookings (yesterday and two days before yesterday)
$query = "SELECT b.BookingID, u.Username, r.Source, r.Destination, b.DepartureDate, b.paymentStatus 
          FROM bookings b 
          JOIN Users u ON b.UserID = u.UserID 
          JOIN routes r ON b.RouteID = r.RouteID 
          WHERE b.DepartureDate IN (:yesterday, :two_days_ago)";
$stmt = $conn->prepare($query);
$stmt->bindParam(':yesterday', $yesterday);
$stmt->bindParam(':two_days_ago', $two_days_ago);
$stmt->execute();
$pastBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming bookings (tomorrow and beyond)
$query = "SELECT b.BookingID, u.Username, r.Source, r.Destination, b.DepartureDate, b.paymentStatus 
          FROM bookings b 
          JOIN Users u ON b.UserID = u.UserID 
          JOIN routes r ON b.RouteID = r.RouteID 
          WHERE b.DepartureDate >= :tomorrow";
$stmt = $conn->prepare($query);
$stmt->bindParam(':tomorrow', $tomorrow);
$stmt->execute();
$upcomingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Express Admin</title>
    <link rel="stylesheet" href="./css/admin-home.css">
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .status { padding: 5px; border-radius: 5px; }
        .status.confirmed { background-color: #4CAF50; color: white; }
        .status.pending { background-color: #FFC107; color: black; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="navBar">
                <div class="logo">Travel Express Admin</div>
                <nav>
                    <a href="#">Dashboard</a>
                    <a href="#">Check Booking</a>
                    <a href="#">Verify Deposit</a>
                    <a href="#">Manage Route</a>
                    <div class="profile-pic"><?php echo htmlspecialchars($initials); ?></div>
                </nav>
            </div>        
        </header>
        <hr/>
        <main>
            <h1>Welcome, Admin!</h1>
            <div class="stats">
                <div class="stat">
                    <h2>Total Users</h2>
                    <p><?php echo htmlspecialchars($totalUsers); ?></p>
                </div>
                <div class="stat">
                    <h2>Tickets Sold</h2>
                    <p><?php echo htmlspecialchars($totalPaidBookings); ?></p>
                </div>
                <div class="stat">
                    <h2>Active Buses</h2>
                    <p><?php echo htmlspecialchars($totalBuses); ?></p>
                </div>
                <div class="stat">
                    <h2>Pending Requests</h2>
                    <p><?php echo htmlspecialchars($totalPendingBookings); ?></p>
                </div>
            </div>
            <div class="bookings">
                <div class="tabs">
                    <button class="tab active" data-tab="current">Current</button>
                    <button class="tab" data-tab="upcoming">Upcoming</button>
                    <button class="tab" data-tab="past">Past</button>
                </div>
                <div class="tab-content active" id="current">
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>User</th>
                                <th>Departure</th>
                                <th>Destination</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($currentBookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['BookingID']); ?></td>
                                <td><?php echo htmlspecialchars($booking['Username']); ?></td>
                                <td><?php echo htmlspecialchars($booking['Source']); ?></td>
                                <td><?php echo htmlspecialchars($booking['Destination']); ?></td>
                                <td><?php echo htmlspecialchars($booking['DepartureDate']); ?></td>
                                <td class="status <?php echo htmlspecialchars($booking['paymentStatus']); ?>"><?php echo htmlspecialchars($booking['paymentStatus']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="tab-content" id="upcoming">
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>User</th>
                                <th>Departure</th>
                                <th>Destination</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingBookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['BookingID']); ?></td>
                                <td><?php echo htmlspecialchars($booking['Username']); ?></td>
                                <td><?php echo htmlspecialchars($booking['Source']); ?></td>
                                <td><?php echo htmlspecialchars($booking['Destination']); ?></td>
                                <td><?php echo htmlspecialchars($booking['DepartureDate']); ?></td>
                                <td class="status <?php echo htmlspecialchars($booking['paymentStatus']); ?>"><?php echo htmlspecialchars($booking['paymentStatus']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="tab-content" id="past">
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>User</th>
                                <th>Departure</th>
                                <th>Destination</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pastBookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['BookingID']); ?></td>
                                <td><?php echo htmlspecialchars($booking['Username']); ?></td>
                                <td><?php echo htmlspecialchars($booking['Source']); ?></td>
                                <td><?php echo htmlspecialchars($booking['Destination']); ?></td>
                                <td><?php echo htmlspecialchars($booking['DepartureDate']); ?></td>
                                <td class="status <?php echo htmlspecialchars($booking['paymentStatus']); ?>"><?php echo htmlspecialchars($booking['paymentStatus']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');

                    const target = tab.getAttribute('data-tab');
                    tabContents.forEach(content => {
                        content.classList.remove('active');
                        if (content.getAttribute('id') === target) {
                            content.classList.add('active');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>
