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
// Assuming 'id' is the session variable set upon login
$user_logged_in = isset($_SESSION['id']);
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
$deposit = $user['Deposit'];

// Take username's first two letters, capitalize them, and store them in a variable called $initials
$initials = strtoupper(substr($username, 0, 2));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Express</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/home.css">
</head>
<body>
    <div class="navbar">
        <div class="logo">Travel Express</div>
        <div class="book-ticket" style="margin-left: 150px;"><a href="deposit.php">Deposit</a></div>
        <div class="book-ticket" style="margin-left: 30px;"><a href="mybooking.php">My bookings</a></div>
        <div class="ml-auto luu" style="width:500px; display: flex; gap:25px; align-items:center;">
            <a href="edit-user.php" class="btn btn-outline-primary mr-2">Profile</a>
            <a href="#footer" class="btn btn-outline-primary mr-2">Contact</a>            
            <a href="logout.php" class="btn btn-secondary">Logout</a>
            <div style="border-radius: 50%; padding: 10px; background-color:#007bff; color:white; font-weight:bold;"> <?php echo htmlspecialchars($initials); ?></div>
            <div id="balance" style=" color: green; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif; font-weight: bold;"> ETB <?php echo htmlspecialchars($deposit); ?></div>
        </div>
    </div>
    <hr/>
    <div class="container hero-section text-center">
        <img src="https://static.vecteezy.com/system/resources/previews/026/977/278/large_2x/bus-of-a-beautiful-transportation-with-futuristic-design-ai-generated-photo.jpg" 
        class="img-fluid" alt="Bus Image">
        <div>
            <div class="hero-text mt-5">Travel by bus with BusGo</div>
        <div class="hero-subtext mt-5">Book tickets, track your bus in real time and earn points</div>
        <div class="hero-search input-group mt-3 mx-auto">
            <a href="booking.php" class="btn btn-primary mt-5 p-2" style="margin-left:50%;" >Book now</a>
        </div>
        </div>
    </div>
    
    <div class="container features-section">
        <h2 class="hero-text">Why BusGo</h2>
        <p class="text-muted mb-5">We're here to help you travel comfortably and safely.</p>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card feature-card">
                    <img src="https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=2069&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" 
                    class="card-img-top" alt="On-time guarantee">
                    <div class="card-body text-center">
                        <div class="feature-card-title">On-time guarantee</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card">
                    <img src="https://images.unsplash.com/photo-1572016047668-5b5e909e1605?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" 
                    class="card-img-top" alt="Real-time tracking">
                    <div class="card-body text-center">
                        <div class="feature-card-title">Real-time tracking</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card">
                    <img src="https://plus.unsplash.com/premium_photo-1671462505492-03f9682bff61?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" 
                    class="card-img-top" alt="Free Wi-Fi and power outlets">
                    <div class="card-body text-center">
                        <div class="feature-card-title">Free Wi-Fi and power outlets</div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
    <div class="container features-section">
        <h2 class="hero-text">Popular Routes</h2>
        <p class="text-muted mb-5">We're here to help you travel comfortably and safely.</p>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card feature-card">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/1b/Addis_abeba_meskele_square_%28cropped%29.jpg/272px-Addis_abeba_meskele_square_%28cropped%29.jpg" 
                    class="card-img-top" alt="On-time guarantee">
                    <div class="card-body text-center">
                        <div class="feature-card-title">Addis Ababa</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card">
                    <img src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/15/59/a7/bd/l-hotel-domine-le-lac.jpg?w=1400&h=1400&s=1" 
                    class="card-img-top" alt="Real-time tracking">
                    <div class="card-body text-center">
                        <div class="feature-card-title">Hawassa</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card">
                    <img src="https://borkena.com/wp-content/uploads/2024/03/Bahir-Dar-Security-Update.jpg" 
                    class="card-img-top" alt="Free Wi-Fi and power outlets">
                    <div class="card-body text-center">
                        <div class="feature-card-title">Bahirdar</div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <footer class="container footer-section" id="footer">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="footer-title">About Us</div>
                <ul class="list-unstyled">
                    <li><a href="#">Company Information</a></li>
                    <li><a href="#">Career Opportunities</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms & Conditions</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <div class="footer-title">Support</div>
                <ul class="list-unstyled">
                    <li><a href="#">Customer Service</a></li>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Report an Issue</a></li>
                    <li><a href="#">Travel Alerts</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <div class="footer-title">Contact Us</div>
                <ul class="list-unstyled">
                    <li><a href="#">Email Us</a></li>
                    <li><a href="#">Call Us</a></li>
                    <li><a href="#">Follow Us</a></li>
                    <li><a href="#">Locations</a></li>
                </ul>
            </div>
        </div>
        <div class="text-center py-3">
            &copy; 2023 Travel Express. All rights reserved.
        </div>
    </footer>
</body>
</html>
