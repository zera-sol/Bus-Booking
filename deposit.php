<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

require_once './database/database.php';

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
    $query = "INSERT INTO deposit (Amount, Receipt, IsVerified) VALUES (:amount, :receipt, :isVerified)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':receipt', $receiptContent, PDO::PARAM_LOB);
    $stmt->bindValue(':isVerified', false, PDO::PARAM_BOOL);

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
    <title>BusGo</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">   
    <link rel="stylesheet" href="./css/booking.css">
    <link rel="stylesheet" href="./css/home.css">
    <link rel="stylesheet" href="./css/deposit.css">
</head>
<body>
    <div class="navbar">
        <div class="logo">Travel Express</div>
        <div class="book-ticket" style="margin-left: 150px;"><a href="#">Deposit</a></div>
        <div class="book-ticket" style="margin-left: 30px;"><a href="mybooking.php">My bookings</a></div>
        <div class="ml-auto luu" style="width:500px; display: flex; gap:25px; align-items:center;">
            <a href="homeloggedin.php" class="btn btn-outline-primary mr-2 btn-secondary">Home</a>
            <a href="#" class="btn btn-outline-primary mr-2 btn-secondary">Help</a>
            <a href="#footer" class="btn btn-outline-primary mr-2 btn-secondary">Contact</a></div>
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
</body>
</html>

