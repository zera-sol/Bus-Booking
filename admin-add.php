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

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        if (isset($_POST['plate_number'])) {
            // Handling Bus form submission
            $plateNumber = $_POST['plate_number'];

            // Check if the bus already exists
            $stmt = $conn->prepare("SELECT * FROM Buses WHERE PlateNumber = :plate_number");
            $stmt->bindParam(':plate_number', $plateNumber);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $message = "Bus already exists.";
            } else {
                // Insert the new bus
                $stmt = $conn->prepare("INSERT INTO Buses (PlateNumber) VALUES (:plate_number)");
                $stmt->bindParam(':plate_number', $plateNumber);

                if ($stmt->execute()) {
                    $message = "Bus added successfully.";
                } else {
                    $message = "Failed to add bus.";
                }
            }
        } elseif (isset($_POST['source'], $_POST['destination'], $_POST['driver_name'], $_POST['bus_plate_number'])) {
            // Handling Route form submission
            $source = $_POST['source'];
            $destination = $_POST['destination'];
            $driverName = $_POST['driver_name'];
            $plateNumber = $_POST['bus_plate_number'];

            // Check if the bus exists and is not referenced in routes
            $stmt = $conn->prepare("SELECT BusID FROM Buses WHERE PlateNumber = :plate_number");
            $stmt->bindParam(':plate_number', $plateNumber);
            $stmt->execute();
            $bus = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($bus) {
                // Check if the bus is already referenced in routes
                $stmt = $conn->prepare("SELECT * FROM Routes WHERE BusID = :bus_id");
                $stmt->bindParam(':bus_id', $bus['BusID']);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $message = "Bus is already assigned to another route.";
                } else {
                    // Insert the new route
                    $stmt = $conn->prepare("INSERT INTO Routes (Source, Destination, DriverName, BusID) VALUES (:source, :destination, :driver_name, :bus_id)");
                    $stmt->bindParam(':source', $source);
                    $stmt->bindParam(':destination', $destination);
                    $stmt->bindParam(':driver_name', $driverName);
                    $stmt->bindParam(':bus_id', $bus['BusID']);

                    if ($stmt->execute()) {
                        $message = "Route added successfully.";
                    } else {
                        $message = "Failed to add route.";
                    }
                }
            } else {
                // Insert the new bus first
                $stmt = $conn->prepare("INSERT INTO Buses (PlateNumber) VALUES (:plate_number)");
                $stmt->bindParam(':plate_number', $plateNumber);

                if ($stmt->execute()) {
                    $busID = $conn->lastInsertId();
                    // Insert the new route
                    $stmt = $conn->prepare("INSERT INTO Routes (Source, Destination, DriverName, BusID) VALUES (:source, :destination, :driver_name, :bus_id)");
                    $stmt->bindParam(':source', $source);
                    $stmt->bindParam(':destination', $destination);
                    $stmt->bindParam(':driver_name', $driverName);
                    $stmt->bindParam(':bus_id', $busID);

                    if ($stmt->execute()) {
                        $message = "Route and bus added successfully.";
                    } else {
                        $message = "Failed to add route.";
                    }
                } else {
                    $message = "Failed to add bus.";
                }
            }
        } elseif (isset($_POST['delete_plate_number'])) {
            // Handling Delete Bus form submission
            $plateNumber = $_POST['delete_plate_number'];

            // Check if the bus exists
            $stmt = $conn->prepare("SELECT * FROM Buses WHERE PlateNumber = :plate_number");
            $stmt->bindParam(':plate_number', $plateNumber);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Attempt to delete the bus
                $stmt = $conn->prepare("DELETE FROM Buses WHERE PlateNumber = :plate_number");
                $stmt->bindParam(':plate_number', $plateNumber);

                if ($stmt->execute()) {
                    $message = "Bus deleted successfully.";
                } else {
                    throw new PDOException("Failed to delete bus.");
                }
            } else {
                $message = "Bus not found.";
            }
        } elseif (isset($_POST['delete_source'], $_POST['delete_destination'])) {
            // Handling Delete Route form submission
            $source = $_POST['delete_source'];
            $destination = $_POST['delete_destination'];

            // Check if the route exists
            $stmt = $conn->prepare("SELECT * FROM Routes WHERE Source = :source AND Destination = :destination");
            $stmt->bindParam(':source', $source);
            $stmt->bindParam(':destination', $destination);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Delete the route
                $stmt = $conn->prepare("DELETE FROM Routes WHERE Source = :source AND Destination = :destination");
                $stmt->bindParam(':source', $source);
                $stmt->bindParam(':destination', $destination);

                if ($stmt->execute()) {
                    $message = "Route deleted successfully.";
                } else {
                    throw new PDOException("Failed to delete route.");
                }
            } else {
                $message = "Route not found.";
            }
        }
    } catch (PDOException $e) {
        // Check for foreign key constraint violation
        if ($e->getCode() == 23000) {
            if (isset($plateNumber)) {
                // Find the route that references the bus
                $stmt = $conn->prepare("SELECT Source, Destination FROM Routes WHERE BusID = (SELECT BusID FROM Buses WHERE PlateNumber = :plate_number)");
                $stmt->bindParam(':plate_number', $plateNumber);
                $stmt->execute();
                $route = $stmt->fetch(PDO::FETCH_ASSOC);
                $source = $route['Source'];
                $destination = $route['Destination'];
                $message = "This Plate Number ($plateNumber) cannot be deleted since it is assigned for the route from $source to $destination.";
            } else {
                $message = "Foreign key constraint violation.";
            }
        } else {
            $message = "Error: " . $e->getMessage();
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
            <a href="admin-cancell.php">Cancel ticket</a>
            <a href="admin-routes.php">Manage Route</a>
            <a href="admin-add.php">change Tables</a>            
            <a href="logout.php">Logout</a>
            <div class="profile-pic"><?php echo htmlspecialchars($initials); ?></div>
        </nav>
    </div>        
</header>
<main>
    <div class="container">
        <div class="search-container">
            <h1>Manage Tables</h1>
            <div class="order-list">
                <div id="message" class="message <?php echo isset($message) && strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <!-- on Bus table -->
                <div class="deposit-item">
                    <button type="button" onclick="toggleDetails(2)">
                        <p style='margin-left:30%;'> Add Bus</p>
                    </button>
                    <div id="details-2" class="deposit-details">
                        <form method="post" action="">
                            <div class="route-inputs">
                                <label for="plate_number">Plate Number:</label>
                                <input type="text" id="plate_number" name="plate_number" required><br />
                            </div>
                            <button type="submit" id="update">Update</button>
                        </form>
                    </div>
                </div>

                <!-- on Routes table -->
                <div class="deposit-item">
                    <button type="button" onclick="toggleDetails(1)">
                        <p style='margin-left:30%;'>Add Route</p>
                    </button>
                    <div id="details-1" class="deposit-details">
                        <form method="post" action="">
                            <div class="route-inputs">
                                <label for="source">Source:</label>
                                <input type="text" id="source" name="source" required><br />
                            </div>
                            <div class="route-inputs">
                                <label for="destination">Destination:</label>
                                <input type="text" id="destination" name="destination" required><br />
                            </div>
                            <div class="route-inputs">
                                <label for="driver_name">Driver Name:</label>
                                <input type="text" id="driver_name" name="driver_name" required><br />
                            </div>
                            <div class="route-inputs">
                                <label for="bus_plate_number">Bus Plate Number:</label>
                                <input type="text" id="bus_plate_number" name="bus_plate_number" required><br />
                            </div>
                            <button type="submit" id="update">Update</button>
                        </form>
                    </div>
                </div>

                <!-- Delete Bus -->
                <div class="deposit-item">
                    <button type="button" onclick="toggleDetails(3)">
                        <p style='margin-left:30%;'>Delete Bus</p>
                    </button>
                    <div id="details-3" class="deposit-details">
                        <form method="post" action="">
                            <div class="route-inputs">
                                <label for="delete_plate_number">Plate Number:</label>
                                <input type="text" id="delete_plate_number" name="delete_plate_number" required><br />
                            </div>
                            <button type="submit" id="update">Done</button>
                        </form>
                    </div>
                </div>

                <!-- Delete Route -->
                <div class="deposit-item">
                    <button type="button" onclick="toggleDetails(4)">
                        <p style='margin-left:30%;'>Delete Route</p>
                    </button>
                    <div id="details-4" class="deposit-details">
                        <form method="post" action="">
                            <div class="route-inputs">
                                <label for="delete_source">Source:</label>
                                <input type="text" id="delete_source" name="delete_source" required><br />
                            </div>
                            <div class="route-inputs">
                                <label for="delete_destination">Destination:</label>
                                <input type="text" id="delete_destination" name="delete_destination" required><br />
                            </div>
                            <button type="submit" id="update">Done</button>
                        </form>
                    </div>
                </div>
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