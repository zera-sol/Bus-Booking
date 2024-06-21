<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once './database/database.php';

$id = $_SESSION['id'];

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
    <title>Travel Express </title>
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
    <footer>
        <div id="terms">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
        <div id="socialMediaIcons">
        <a href="#">
            <div class="text-[#A18249]" data-icon="LinkedinLogo" data-size="24px" data-weight="regular">
                <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
                <path
                    d="M216,24H40A16,16,0,0,0,24,40V216a16,16,0,0,0,16,16H216a16,16,0,0,0,16-16V40A16,16,0,0,0,216,24Zm0,192H40V40H216V216ZM96,112v64a8,8,0,0,1-16,0V112a8,8,0,0,1,16,0Zm88,28v36a8,8,0,0,1-16,0V140a20,20,0,0,0-40,0v36a8,8,0,0,1-16,0V112a8,8,0,0,1,15.79-1.78A36,36,0,0,1,184,140ZM100,84A12,12,0,1,1,88,72,12,12,0,0,1,100,84Z"
                ></path>
                </svg>
            </div>
    </a>
    <a href="#">
        <div class="text-[#A18249]" data-icon="TwitterLogo" data-size="24px" data-weight="regular">
            <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
            <path
                d="M247.39,68.94A8,8,0,0,0,240,64H209.57A48.66,48.66,0,0,0,168.1,40a46.91,46.91,0,0,0-33.75,13.7A47.9,47.9,0,0,0,120,88v6.09C79.74,83.47,46.81,50.72,46.46,50.37a8,8,0,0,0-13.65,4.92c-4.31,47.79,9.57,79.77,22,98.18a110.93,110.93,0,0,0,21.88,24.2c-15.23,17.53-39.21,26.74-39.47,26.84a8,8,0,0,0-3.85,11.93c.75,1.12,3.75,5.05,11.08,8.72C53.51,229.7,65.48,232,80,232c70.67,0,129.72-54.42,135.75-124.44l29.91-29.9A8,8,0,0,0,247.39,68.94Zm-45,29.41a8,8,0,0,0-2.32,5.14C196,166.58,143.28,216,80,216c-10.56,0-18-1.4-23.22-3.08,11.51-6.25,27.56-17,37.88-32.48A8,8,0,0,0,92,169.08c-.47-.27-43.91-26.34-44-96,16,13,45.25,33.17,78.67,38.79A8,8,0,0,0,136,104V88a32,32,0,0,1,9.6-22.92A30.94,30.94,0,0,1,167.9,56c12.66.16,24.49,7.88,29.44,19.21A8,8,0,0,0,204.67,80h16Z"
            ></path>
            </svg>
        </div>
    </a>
    <a href="#">
      <div class="text-[#A18249]" data-icon="InstagramLogo" data-size="24px" data-weight="regular">
        <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
          <path
            d="M128,80a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,80Zm0,80a32,32,0,1,1,32-32A32,32,0,0,1,128,160ZM176,24H80A56.06,56.06,0,0,0,24,80v96a56.06,56.06,0,0,0,56,56h96a56.06,56.06,0,0,0,56-56V80A56.06,56.06,0,0,0,176,24Zm40,152a40,40,0,0,1-40,40H80a40,40,0,0,1-40-40V80A40,40,0,0,1,80,40h96a40,40,0,0,1,40,40ZM192,76a12,12,0,1,1-12-12A12,12,0,0,1,192,76Z"
          ></path>
        </svg>
      </div>
    </a>
        </div>
        <h2>&copy;2024 Travel Express Admin</h2>
    </footer>
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
