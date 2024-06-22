<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
} else if ($_SESSION['role'] != 'user') {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['id'];

require_once './database/database.php';

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

// Take username's first two letters, capitalize them, and store them in a variable called $initials
$initials = strtoupper(substr($username, 0, 2));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get the amount from the form
        $amount = $_POST['amount'];

        // Handle the file upload
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == UPLOAD_ERR_OK) {
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
                $_SESSION['message'] = "Deposit information uploaded successfully.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "There was an error uploading the deposit information.";
                $_SESSION['message_type'] = "error";
            }
        } else {
            $_SESSION['message'] = "Please upload a valid receipt.";
            $_SESSION['message_type'] = "error";
        }
    } catch (Exception $e) {
        $_SESSION['message'] = "An error occurred: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
    header("Location: deposit.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Express</title> 
    <link rel="stylesheet" href="./css/navbar.css">
    <link rel="stylesheet" href="./css/deposit.css">
    <style>
       
       #message {
            text-align: center;
            padding:10px;
            margin: 20px 0;
        }
        .alert-success {
            background-color: lightgreen;
            color: white;
        }
        .alert-danger {
            color: white;
            background-color: red;
        }
    </style>
    <script>
        function hideMessage() {
            setTimeout(function() {
                var messageElement = document.getElementById('message');
                if (messageElement) {
                    messageElement.style.display = 'none';
                }
            }, 8000); // 8000 milliseconds = 8 seconds
        }
        window.onload = hideMessage;
    </script>
</head>
<body>
    <!-- NavBars of a User -->
    <div class="navbar" style="padding:0; margin:0;">
        <div class="logo" style="font-weight: bold; font-size: 1.5rem;">Travel Express</div>
        <div class="laa" style="margin-left: 120px; padding: 5px; border-radius: 5px;"><a href="deposit.php" style="text-decoration: none;">Deposit</a></div>
        <div class="laa" style="margin-left: 30px; padding: 5px; border-radius: 5px;"><a href="draft.php" style="text-decoration: none;">Draft</a></div>
        <div class="laa" style="margin-left: 30px; padding: 5px; border-radius: 5px;"><a href="mybooking.php" style="text-decoration: none;">Tickets</a></div>
        <div class="luu" style="width:500px; display: flex; gap:35px; align-items:center; margin-left: 250px;">
            <a href="edit-user.php" class="not-logout">Profile</a>
            <a href="homeloggedin.php" class="not-logout">Home</a>            
            <a href="home.php" style=" background-color: rgb(76, 76, 76); color: white;">Logout</a>
            <div style="border-radius: 50%; padding: 10px; background-color:rgb(0, 0, 226); color:white; font-weight:bold;"><?php echo htmlspecialchars($initials); ?></div>
            <div id="balance" style=" color: green; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif; font-weight: bold;"> ETB <?php echo htmlspecialchars($deposit); ?></div>
        </div>
    </div>
    <hr/>
    <div class="upload-container" style="margin-top: 50px;">
        <h2>How to Deposit to an account?</h2>
        <p>1. First send the amount of Birr you want to deposit to <span>CBE 1000292619891</span></p>
        <p>2. Then take a photo of the receipt that has been given from the bank and upload on the space provided below</p>
        <div class="upload-title">Upload Deposit Receipt</div>
        <?php if (isset($_SESSION['message'])): ?>
            <div id="message" class="alert <?php echo $_SESSION['message_type'] == 'success' ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
            </div>
        <?php endif; ?>
        <form action="deposit.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="amount">Deposit amount:</label>
                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required min="1"
                style="width:100%; border:2px solid #f1f1f1; border-radius:10px; padding:10px; margin:10px;">
            </div>
            <div class="form-group file-upload">
                <button class="file-upload-btn" type="button" onclick="document.getElementById('receipt').click()">Choose image</button>
                <input type="file" id="receipt" name="receipt" accept="image/*" required
                      style="width:100%; border-radius:10px; padding:10px; margin:10px;">
            </div>
            <button type="submit" style="width:14%; border:2px solid #f1f1f1; border-radius:10px; padding:10px; margin-left:40%;">Upload</button>
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
