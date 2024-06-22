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

$error = '';
$success = '';

$id = $_SESSION['id'];

// Create an instance of the Database class
$database = new Database();
$conn = $database->conn;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['first-name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $oldPasswordInput = trim($_POST['old-password']);
    $newPassword = trim($_POST['new-password']);
    $confirmPassword = trim($_POST['confirm-password']);
    
    // Fetch current user data
    $stmt = $conn->prepare("SELECT * FROM Users WHERE UserID = :userid");
    $stmt->bindParam(':userid', $id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error = 'User not found.';
    } else {
        // Verify if the new username or email is already taken by another user
        $stmt = $conn->prepare("SELECT * FROM Users WHERE (Username = :username OR Email = :email) AND UserID != :userid");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':userid', $id);
        $stmt->execute();
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            if ($existingUser['Username'] == $username) {
                $error = 'The username is already taken.';
            } elseif ($existingUser['Email'] == $email) {
                $error = 'The email is already in use.';
            }
        } else {
            // Verify old password if a new password is provided
            if (!empty($newPassword) || !empty($confirmPassword)) {
                if (password_verify($oldPasswordInput, $user['Password'])) {
                    if ($newPassword === $confirmPassword) {
                        // Hash the new password
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    } else {
                        $error = 'New password and confirm password do not match.';
                    }
                } else {
                    $error = 'Old password is incorrect.';
                }
            } else {
                $hashedPassword = $user['Password'];
            }
            
            // Update user information if there are no errors
            if (empty($error)) {
                $stmt = $conn->prepare("UPDATE Users SET Name = :name, Email = :email, Username = :username, Phone = :phone, Password = :password WHERE UserID = :userid");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':userid', $id);
                
                if ($stmt->execute()) {
                    $success = 'Profile updated successfully.';
                } else {
                    $error = 'Failed to update profile.';
                }
            }
        }
    }
}

// Fetch updated user data for the form
$stmt = $conn->prepare("SELECT * FROM Users WHERE UserID = :userid");
$stmt->bindParam(':userid', $id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user['Username'];
$email = $user["Email"];
$phone = $user["Phone"];
$name = $user['Name'];
$role = $user["role"];
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./css/edit-user.css">
    <link rel="stylesheet" href="./css/navbar.css">
</head>
<body>
    <!-- Nav bar -->
<div class="navbar">
        <div class="logo" style="font-weight: bold; font-size: 1.5rem;">Travel Express</div>
        <div class="laa" style="margin-left: 120px; padding: 5px; border-radius: 5px;"><a href="deposit.php" style="text-decoration: none;">Deposit</a></div>
        <div class="laa" style="margin-left: 30px; padding: 5px; border-radius: 5px;"><a href="draft.php" style="text-decoration: none;">Draft</a></div>
        <div class="laa" style="margin-left: 30px; padding: 5px; border-radius: 5px;"><a href="mybooking.php" style="text-decoration: none;">Tickets</a></div>
        <div class="luu" style="width:500px; display: flex; gap:35px; align-items:center; margin-left: 300px;">
            <a href="edit-user.php" class="not-logout">Profile</a>
            <a href="homeloggedin.php" class="not-logout">Home</a>            
            <a href="home.php" style=" background-color: rgb(76, 76, 76); color: white;">Logout</a>
            <div style="border-radius: 50%; padding: 10px; background-color:rgb(0, 0, 226); color:white; font-weight:bold;"><?php echo htmlspecialchars($initials); ?></div>
            <div id="balance" style=" color: green; font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif; font-weight: bold;"> ETB <?php echo htmlspecialchars($deposit); ?></div>
        </div>
    </div>
    <hr/>

 <div style=" max-width:600px; margin:65px auto 0; height: 100vh;">
    <div class="modal">
        <div class="modal-header">
            <div class="profile-icon">
                <i class="fa-solid fa-user"></i>
            </div>
        </div>
        <div class="modal-content">
            <h2>Edit Profile</h2>
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif (!empty($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form action="edit-user.php" method="POST">
                <div id='form-parent'>
                    <div>
                        <div class="form-group">
                            <label for="first-name">Name:</label>
                            <input type="text" id="first-name" name="first-name" value="<?php echo htmlspecialchars($name); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                        <div class="form-group">
                            <label for="status">Role:</label>
                            <input type="text" id="role" name="role" value="<?php echo htmlspecialchars($role); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="old-password">Old Password:</label>
                            <input type="password" id="old-password" name="old-password">
                        </div>
                    </div>
                    <div>
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number:</label>
                            <input type="number" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                        </div>
                        <div class="form-group">
                            <label for="new-password">New Password:</label>
                            <input type="password" id="new-password" name="new-password">
                        </div>
                        <div class="form-group">
                            <label for="confirm-password">Confirm New Password:</label>
                            <input type="password" id="confirm-password" name="confirm-password">
                        </div>
                    </div>
                </div>
                <div class="form-group form-actions" id="submit-btn">
                    <button type="submit">Save & Close</button>
                </div>
            </form>
        </div>
    </div>
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
