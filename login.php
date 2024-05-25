<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database.php file to access the Database class
require_once './database/database.php';

// Initialize error message
$error = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate form data
    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Create an instance of the Database class
        $database = new Database();
        $conn = $database->conn;

        // Check if user exists in the database
        $stmt = $conn->prepare("SELECT * FROM Users WHERE Email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        
        if ($user && password_verify($password, $user['Password'])) {
            // Password is correct, start session and store user info
            $_SESSION['username'] = $user['Username'];
            $_SESSION['email'] = $user['Email'];
            $_SESSION['phone'] = $user['Phone'];
            header("Location: booking.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="parent-container">
        <div id="child-image" class="child-container">
            <img src="https://i.ibb.co/tQkKGjz/Screenshot-80.png" alt="bus emoji">
        </div>
        <div class="child-container child2">
            <div class="busLogo-text">
                <img src="https://www.shutterstock.com/image-vector/bus-icon-vector-template-flat-260nw-1413254132.jpg" width="50" height="50" />
                <h1>Bus Ticket</h1>
             </div>
             <div id="form-container">
                <?php if ($error): ?>
                <p style="background: #F2DEDE; color: #A94442; padding: 10px; width: 500px; 
                    border-radius: 5px; margin: 5px auto;"><?php echo $error; ?></p>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <label for="Email">Email</label>
                    <input type="email" name="email" placeholder="Enter email" required>
                    <label for="password">Password</label>
                    <input type="password" name="password" placeholder="Enter password" required>
                    <button type="submit">Log in</button>
                </form>
             </div>
             <div class="bottom-text">
                <h3>didn't have an account yet?</h3>
                <a href="register.php">Register</a>
            </div>
        </div>
    </div>
</body>
</html>
