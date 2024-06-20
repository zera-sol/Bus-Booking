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

// Handle form submission for updating route
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $routeID = $_POST['route_id'];
    $driverName = $_POST['driver_name'];
    $busPlateNumber = $_POST['bus_plate_number'];

    try {
        $conn->beginTransaction();

        $stmtUpdateRoute = $conn->prepare("UPDATE Routes SET DriverName = :driver_name WHERE RouteID = :route_id");
        $stmtUpdateRoute->bindParam(':route_id', $routeID);
        $stmtUpdateRoute->bindParam(':driver_name', $driverName);

        $stmtUpdateRoute->execute();

        $stmtUpdateBus = $conn->prepare("UPDATE Buses SET PlateNumber = :plate_number WHERE BusID = (SELECT BusID FROM Routes WHERE RouteID = :route_id)");
        $stmtUpdateBus->bindParam(':plate_number', $busPlateNumber);
        $stmtUpdateBus->bindParam(':route_id', $routeID);

        $stmtUpdateBus->execute();

        $conn->commit();
        $message = "Route updated successfully";
    } catch (Exception $e) {
        $conn->rollBack();
        $message = "Failed to update route: " . $e->getMessage();
    }
}

// Fetch routes from the database
$stmtRoutes = $conn->prepare("SELECT r.RouteID, r.Source, r.Destination, r.DriverName, b.PlateNumber FROM Routes r LEFT JOIN Buses b ON r.BusID = b.BusID");
$stmtRoutes->execute();
$routes = $stmtRoutes->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./css/admin-footer.css">
    <link rel="stylesheet" href="./css/admin-routes.css">
    <style>
        .deposit-details {
            display: none;
        }
        .deposit-item {
            margin-bottom: 20px;
        }
        .deposit-item button {
            width: 100%;
            padding: 10px;
            text-align: left;
        }
        .deposit-details img {
            max-width: 50%;
        }
        .message {
            text-align: center;
            margin: 20px 0;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .route-inputs{
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin-bottom: 5px;
        }
        input{
            padding: 5px;
            width:30%;
            border-bottom: 1px solid #A18249;
            background-color: #fff;  
            border-radius: 10px;  
            font-size: 1.3rem;
            padding: 0 30px;
            color: #000;
        }
        input:disabled {
            background-color: #fff; 
            color: #000; 
            border: 1px solid #A18249; 
            cursor: not-allowed; 
        }
        #update{
            padding: 5px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            padding: 10px 20px;
            width: 10%;
        }
        
        
    </style>
    <script>
        function toggleDetails(routeId) {
            var details = document.getElementById('details-' + routeId);
            if (details.style.display === 'none' || details.style.display === '') {
                details.style.display = 'block';
            } else {
                details.style.display = 'none';
            }
        }

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
</head>
<body>
<header>
    <div class="navBar">
        <div class="logo">Travel Express Admin</div>
        <nav>
            <a href="admin-home.php">Dashboard</a>
            <a href="admin-bookings.php">Check Booking</a>
            <a href="admin-verify.php">Verify Deposit</a>
            <a href="#">Manage Route</a>
            <div class="profile-pic"><?php echo htmlspecialchars($initials); ?></div>
        </nav>
    </div>        
</header>
<main>
    <div class="container">
        <div class="search-container">
            <h1>Manage Routes</h1>
            <?php if (isset($message)): ?>
                <div id="message" class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <div class="order-list">
                <?php foreach ($routes as $route): ?>
                    <div class="deposit-item">
                        <button type="button" onclick="toggleDetails(<?php echo $route['RouteID']; ?>)">
                            <p style='margin-left:30%;'><?php echo $route['Source']; ?> - <?php echo $route['Destination']; ?></p>
                        </button>
                        <div id="details-<?php echo $route['RouteID']; ?>" class="deposit-details">
                            <form id="form-<?php echo $route['RouteID']; ?>" method="post" action="">
                                <input type="hidden" name="route_id" value="<?php echo $route['RouteID']; ?>">
                              <div class="route-inputs">
                                <label for="source-<?php echo $route['RouteID']; ?>">From:</label>
                                <input type="text" id="source-<?php echo $route['RouteID']; ?>" name="source" value="<?php echo htmlspecialchars($route['Source']); ?>" disabled><br />
                             </div>
                             <div class="route-inputs">
                                <label for="destination-<?php echo $route['RouteID']; ?>">To:</label>
                                <input type="text" id="destination-<?php echo $route['RouteID']; ?>" name="destination" value="<?php echo htmlspecialchars($route['Destination']); ?>" disabled><br />
                             </div>
                             <div class="route-inputs">
                                <label for="driver-name-<?php echo $route['RouteID']; ?>">Driver Name:</label>
                                <input type="text" id="driver-name-<?php echo $route['RouteID']; ?>" name="driver_name" value="<?php echo htmlspecialchars($route['DriverName']); ?>" ><br />
                             </div>
                             <div class="route-inputs">
                                <label for="bus-plate-number-<?php echo $route['RouteID']; ?>">Bus Plate Number:</label>
                                <input type="text" id="bus-plate-number-<?php echo $route['RouteID']; ?>" name="bus_plate_number" value="<?php echo htmlspecialchars($route['PlateNumber']); ?>" ><br />
                             </div>
                                <button type="submit" id="update">Update</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>
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
</body>
</html>
