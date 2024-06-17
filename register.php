<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './database/database.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fname = $_POST['fname'];
    $email = $_POST['email'];
    $uname = $_POST['uname'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $role = "user";

    if (empty($fname) || empty($email) || empty($uname) || empty($phone) || empty($password) || empty($cpassword)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $cpassword) {
        $error = "Passwords do not match.";
    } else {

        $database = new Database();
        $conn = $database->conn;

        // Check if username already exists
        $stmt = $conn->prepare("SELECT * FROM Users WHERE Username = :uname");
        $stmt->bindParam(':uname', $uname);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $error = "Username already exists.";
        } else {
            // Insert new user into database
            $stmt = $conn->prepare("INSERT INTO Users (Username, Password, Name, Phone, Email, role) VALUES (:uname, :password, :fname, :phone, :email, :role)");
            $stmt->bindParam(':uname', $uname);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':fname', $fname);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);

            if ($stmt->execute()) {
                // Get the user ID of the newly registered user
                $user_id = $conn->lastInsertId();

                // Store the user ID in session and redirect to booking page
                $_SESSION['id'] = $user_id;
                header("Location: homeloggedin.php");
                exit();
            } else {
                $error = "Failed to register. Please try again.";
            }
        }
    }
}
?>

        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Sign Up</title>
            <link rel="stylesheet" href="./css/register.css">
        </head>
        <body>
            <div class="parent-container">
                <h1>Sign up</h1>
                <h4>Enter your details to book a bus ticket</h4>

                <?php if ($error): ?>
                <p style="background: #F2DEDE; color: #A94442; padding: 10px; width: 500px; 
                    border-radius: 5px; margin: 5px auto;"><?php echo $error; ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                <p style="background: #bfeccb; color: #38a66f; padding: 10px; width: 500px; 
                    border-radius: 5px; margin: 5px auto;"><?php echo $success; ?></p>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <label>Full Name</label>
                    <input name="fname" type="text" placeholder='Enter Your full name' required>
                    <label>Email</label>
                    <input name="email" type="email" placeholder='Enter Your email address' required>
                    <label>Username</label>
                    <input name="uname" type="text" placeholder='Create Your username' required>
                    <label>Phone</label>
                    <input name="phone" type="text" placeholder='Enter your phone number' required>
                    <label>Create a secure password</label>
                    <input type="password" name="password" placeholder='Enter Your password' required>
                    <label>Confirm your password</label>
                    <input type="password" name="cpassword" placeholder='Confirm Your password' required>
                    <div class="terms-conditions">
                        <input type="checkbox" style="width: 20px; height: 20px;" required />
                        <label>By signing up, you are agreeing with our terms and condition.</label>
                    </div>
                    <input name="signup_button" value="Sign up" type='submit' />
                    <h4 id="have-account">Already have an account? <a href="login.php">Log in</a></h4>
                </form>
            </div>
        </body>
        </html>
