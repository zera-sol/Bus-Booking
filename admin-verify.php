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

$verificationMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify'])) {
        $depositId = $_POST['deposit_id'];

        // Begin transaction
        $conn->beginTransaction();

        try {
            // Update isVerified to true
            $stmt = $conn->prepare("UPDATE Deposit SET isVerified = 1 WHERE DepositID = :deposit_id");
            $stmt->bindParam(':deposit_id', $depositId);
            $stmt->execute();

            // Get the amount and user ID from the deposit
            $stmt = $conn->prepare("SELECT Amount, UserID FROM Deposit WHERE DepositID = :deposit_id");
            $stmt->bindParam(':deposit_id', $depositId);
            $stmt->execute();
            $deposit = $stmt->fetch(PDO::FETCH_ASSOC);

            $amount = $deposit['Amount'];
            $userId = $deposit['UserID'];

            // Add the amount to the user's deposit value
            $stmt = $conn->prepare("UPDATE Users SET Deposit = Deposit + :amount WHERE UserID = :userid");
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':userid', $userId);
            $stmt->execute();

            // Commit transaction
            $conn->commit();
            $verificationMessage = 'Deposit verified successfully!';
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            $verificationMessage = 'Failed to verify deposit: ' . $e->getMessage();
        }
    } elseif (isset($_POST['decline'])) {
        $depositId = $_POST['deposit_id'];

        try {
            // Delete the deposit entry
            $stmt = $conn->prepare("DELETE FROM Deposit WHERE DepositID = :deposit_id");
            $stmt->bindParam(':deposit_id', $depositId);
            $stmt->execute();

            $verificationMessage = 'Deposit request declined and deleted successfully.';
        } catch (Exception $e) {
            $verificationMessage = 'Failed to decline deposit: ' . $e->getMessage();
        }
    }
}

$stmt = $conn->prepare("SELECT Deposit.DepositID, Deposit.Amount, Deposit.Receipt, Users.Username 
                        FROM Deposit
                        JOIN Users ON Deposit.UserID = Users.UserID 
                        WHERE Deposit.isVerified = 0");
$stmt->execute();
$deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Express</title>
    <link rel="stylesheet" href="./css/admin-footer.css">
    <link rel="stylesheet" href="./css/admin-verify.css">
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
        .loading-spinner {
            display: none;
            border: 8px solid #f3f3f3;
            border-radius: 50%;
            border-top: 8px solid #3498db;
            width: 50px;
            height: 50px;
            -webkit-animation: spin 2s linear infinite;
            animation: spin 2s linear infinite;
            margin: auto;
            margin-top: 20px;
        }
        #decline{
            background-color: red;
            color: white;
            width: 30%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
    </style>
    <script>
        function toggleDetails(depositId) {
            var details = document.getElementById('details-' + depositId);
            if (details.style.display === 'none' || details.style.display === '') {
                details.style.display = 'block';
            } else {
                details.style.display = 'none';
            }
        }

        function showSpinner() {
            document.getElementById('loading-spinner').style.display = 'block';
        }
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
    <div id="loading-spinner" class="loading-spinner"></div>
            <?php if ($verificationMessage): ?>
                <div class="message <?php echo strpos($verificationMessage, 'Failed') === false ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($verificationMessage); ?>
                </div>
            <?php endif; ?>
        <div class="search-container">
            <h1>Unverified Deposit Requests</h1>
            <div class="order-list">
                <?php if (empty($deposits)): ?>
                    <p>No unverified deposits found</p>
                <?php else: ?>
                    <?php foreach ($deposits as $deposit): ?>
                        <div class="deposit-item">
                            <button onclick="toggleDetails(<?php echo htmlspecialchars($deposit['DepositID']); ?>)">
                               <p><?php echo htmlspecialchars($deposit['Username']); ?></p> - <p>#<?php echo htmlspecialchars($deposit['DepositID']); ?></p>
                            </button>
                            <div id="details-<?php echo htmlspecialchars($deposit['DepositID']); ?>" class="deposit-details">
                                <p>Deposit ID: #<span> <?php echo htmlspecialchars($deposit['DepositID']); ?> </span> </p>
                                <p>Username: <span><?php echo htmlspecialchars($deposit['Username']); ?> </span> </p>
                                <p>Amount: ETB <span><?php echo htmlspecialchars($deposit['Amount']); ?> </span> </p>
                                <div>
                                    <?php if (!empty($deposit['Receipt'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($deposit['Receipt']); ?>" alt="Receipt Image">
                                    <?php else: ?>
                                        <p>No receipt available</p>
                                    <?php endif; ?>
                                </div>
                                <form method="post" action="" onsubmit="showSpinner()">
                                    <input type="hidden" name="deposit_id" value="<?php echo htmlspecialchars($deposit['DepositID']); ?>">
                                    <button type="submit" name="verify" id="verify">Verify</button>
                                    <input type="hidden" name="deposit_id" value="<?php echo htmlspecialchars($deposit['DepositID']); ?>">
                                    <button type="submit" name="decline" id="decline">Decline</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
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
