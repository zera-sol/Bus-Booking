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
$id = $_SESSION['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Create an instance of the Database class
    $database = new Database();
    $conn = $database->conn;

    // Get the amount from the form
    $amount = $_POST['amount'];

    // Handle the file upload
    $receipt = $_FILES['receipt']['tmp_name'];
    $receiptContent = file_get_contents($receipt);

    // Prepare the SQL statement
    $query = "INSERT INTO deposit (Amount, Receipt, IsVerified, UserID) VALUES (:amount, :receipt, :isVerified, :id)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':receipt', $receiptContent, PDO::PARAM_LOB);
    $stmt->bindValue(':isVerified', false, PDO::PARAM_BOOL);
    $stmt->bindParam(':id', $id);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Deposit information uploaded successfully.";
        header("Location: homeloggedin.php");
    } else {
        echo "There was an error uploading the deposit information.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Express</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">   
    <link rel="stylesheet" href="./css/booking.css">
    <link rel="stylesheet" href="./css/home.css">
    <link rel="stylesheet" href="./css/deposit.css">
    <link rel="stylesheet" href="./css/navbar.css">
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
            <a href="logout.php" style=" background-color: rgb(76, 76, 76); color: white;">Logout</a>
            <div style="border-radius: 50%; padding: 10px; background-color:#007bff; color:white; font-weight:bold;">ZH</div>
            <div id="balance" style=" color: green; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif; font-weight: bold;"> ETB 45000</div>
        </div>
    </div>
    <hr/>
    <div class="upload-container" style="margin-top: 50px;">
        <h2>How to Deposit to an account?</h2>
        <p>1. First send the amount of Birr you want to deposit to <span>CBE 1000292619891</span></p>
        <p>2.Then take a photo of the receipt that has been given from the bank and upload on the space provided bellow</p>
        <div class="upload-title">Upload Deposit Receipt</div>
        <form action="deposit.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="amount">Deposit amount:</label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
            </div>
            <div class="form-group file-upload">
                <button class="file-upload-btn" type="button" onclick="document.getElementById('receipt').click()">Choose image</button>
                <input type="file" id="receipt" name="receipt" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Upload</button>
        </form>
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

