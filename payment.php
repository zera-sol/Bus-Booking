    <?php
    session_start();
    require_once './database/database.php';

    // Check if booking ID is set in the session
    if (!isset($_SESSION['booking_id'])) {
        header("Location: booking.php");
        exit();
    }
    else if($_SESSION['role'] != 'user'){
        header("Location: login.php");
        exit();
    }
    // Retrieve the booking ID
    $bookingID = $_SESSION['booking_id'];

    // Create an instance of the Database class
    $database = new Database();
    $conn = $database->conn;
    
    // Retrieve route ID and departure date for the booking
    $query = "SELECT RouteID, DepartureDate FROM bookings WHERE BookingID = :bookingID";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':bookingID', $bookingID);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        echo "Invalid booking ID.";
        exit();
    }

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $routeID = $row['RouteID'];
    $departureDate = $row['DepartureDate'];

    // Retrieve booked seats for the specified route and date
    $query = "SELECT SeatNumber FROM bookings WHERE RouteID = :routeID AND DepartureDate = :departureDate";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':routeID', $routeID);
    $stmt->bindParam(':departureDate', $departureDate);
    $stmt->execute();

    $bookedSeats = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Generate an array of all seats (1-50)
    $allSeats = range(1, 50);

    // Calculate available seats
    $availableSeats = array_diff($allSeats, $bookedSeats);

    $error = '';
    $success = '';

     //taking the price of the specified route
            // first retriving the Users routeId from booking table
                $query1 = "SELECT RouteID FROM bookings WHERE BookingID = :bookingID";
                $stmt1 = $conn->prepare($query1);
                $stmt1->bindParam(':bookingID', $bookingID);
                $stmt1->execute();
                $row1 = $stmt1->fetch(PDO::FETCH_ASSOC);                   
                $routeID = $row1['RouteID'];

           // Second Query: Get the Cost from the Route table using the RouteID
                  $query2 = "SELECT Cost FROM routes WHERE RouteID = :routeID";
                  $stmt2 = $conn->prepare($query2);
                  $stmt2->bindParam(':routeID', $routeID);
                  $stmt2->execute();
                  $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                  $routePrice = $row2['Cost'];

               

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $seatNumber = $_POST['seat_number'];
        $id = $_SESSION['id']; 

        $stmt = $conn->prepare("SELECT * FROM Users WHERE UserID = :userid");
        $stmt->bindParam(':userid', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $username = $user['Username'];

        if (in_array($seatNumber, $availableSeats)) {
            // Check user's deposit
            $query = "SELECT u.Deposit, r.Cost FROM users u JOIN routes r ON r.RouteID = :routeID WHERE u.Username = :username";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':routeID', $routeID);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['Deposit'] >= $result['Cost']) {
                // Update booking table
                $query = "UPDATE bookings SET SeatNumber = :seatNumber, PaymentStatus = 'Paid' WHERE BookingID = :bookingID";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':seatNumber', $seatNumber);
                $stmt->bindParam(':bookingID', $bookingID);

                if ($stmt->execute()) {
                    // Deduct cost from user's deposit
                    $newDeposit = $result['Deposit'] - $result['Cost'];
                    $query = "UPDATE users SET Deposit = :newDeposit WHERE Username = :username";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':newDeposit', $newDeposit);
                    $stmt->bindParam(':username', $username);

                    if ($stmt->execute()) {                    
                        $_SESSION['success_message'] = "Booking successful! Your seat number is $seatNumber.";
                        header("Location: booking.php");
                        exit();
                    } else {
                        $error = "Failed to update user's deposit.";
                    }
                } else {
                    $error = "Failed to update booking.";
                }
            } else {
                $error = "Insufficient deposit to complete the booking.";
            }
        } else {
            $error = "Selected seat number is not available.";
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
    <link rel="stylesheet" href="./css/home.css">
    <link rel="stylesheet" href="./css/booking.css">
    <link rel="stylesheet" href="./css/payment.css">
    
</head>
<body>
    <div class="navbar">
        <div class="logo">Travel Express</div>
        <div class="book-ticket" style="margin-left: 150px;"><a href="#">Deposit</a></div>
        <div class="book-ticket" style="margin-left: 30px;"><a href="mybooking.php">My bookings</a></div>
        <div class="ml-auto luu" style="width:500px; display: flex; gap:25px; align-items:center;">
            <a href="homeloggedin.php" class="btn btn-outline-primary mr-2 btn-secondary">Home</a>
            <a href="#" class="btn btn-outline-primary mr-2 btn-secondary">Help</a>
            <a href="#footer" class="btn btn-outline-primary mr-2 btn-secondary">Contact</a> 
            </div>
    </div>
    <hr/>
    <div class="content">        
    <div class="message">
            <?php
            if (!empty($error)) {
                echo "<p style='color: red;'>$error</p> </br>
                      <a href='booking.php' id='gohome'>Back Page</a>
                ";
                
            }
            if (!empty($success)) {
                echo "<p style='color: green;'>$success</p>";
            }
            ?>
        </div>
        <div class="available-seat">
            <h1>Available Seats</h1>
            <div class="seats">
                <?php foreach ($allSeats as $seat): ?>
                    <div class="seat <?php echo in_array($seat, $bookedSeats) ? 'booked' : ''; ?>">
                        <?php echo htmlspecialchars($seat); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="trip-form">
            <form action="payment.php" method="POST">
                <label for="seat-number">Seat Number:</label>
                <input type="number" id="seat-number" name="seat_number" min="1" max="50" required>                
                <p id="ticket-price"><span>Ticket Price:</span> ETB <?php echo htmlspecialchars($routePrice); ?></p>
                <button type="submit">Pay and Take Ticket</button>
            </form>
        </div>
        <div class="footer" id="footer" >
            <div class="contact">
                <h3>Contact Us</h3>
                <p>Phone: +251-911-111-111</p>
                <p>Email: traveExpreSs@gmail.org</p>
                <p>Address: Addis Ababa, Ethiopia</p>
                <p>&copy;2021 Travel Express. All rights reserved</p>
            </div>
            <div>
                <h3>Follow Us</h3>
                <p>Facebook</p>
                <p>Twitter</p>
                <p>Instagram</p>
                <p>LinkedIn</p>
            </div>
            <div>
                <h3>Quick Links</h3>
                <p>Home</p>
                <p>Book a trip</p>
                <p>My Booking</p>
                <p>Help</p>
            </div>
        </div>
    </div>

    </body>
    </html>
