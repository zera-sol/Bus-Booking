<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database.php file to access the Database class
require_once 'database.php';

// Create an instance of the Database class
$database = new Database();

// Create Users table
try {
    $sql = "
    CREATE TABLE IF NOT EXISTS Users (
        UserID INT AUTO_INCREMENT PRIMARY KEY,
        Username VARCHAR(50) NOT NULL UNIQUE,
        Password VARCHAR(255) NOT NULL,
        Name VARCHAR(100) NOT NULL,
        Phone VARCHAR(20),
        Email VARCHAR(100) NOT NULL
    )";
    $database->conn->exec($sql);
    echo "Table Users created successfully <br />";
} catch(PDOException $e) {
    echo "Error creating Users table: " . $e->getMessage() . "<br />";
}

// Create Buses table
try {
    $sql = "
    CREATE TABLE IF NOT EXISTS Buses (
        BusID INT AUTO_INCREMENT PRIMARY KEY,
        PlateNumber VARCHAR(20) NOT NULL
    )";
    $database->conn->exec($sql);
    echo "Table Buses created successfully <br />";
} catch(PDOException $e) {
    echo "Error creating Buses table: " . $e->getMessage() . "<br />";
}

// Create Routes table
try {
    $sql = "
    CREATE TABLE IF NOT EXISTS Routes (
        RouteID INT AUTO_INCREMENT PRIMARY KEY,
        Source VARCHAR(100) NOT NULL,
        Destination VARCHAR(100) NOT NULL,
        BusID INT,
        FOREIGN KEY (BusID) REFERENCES Buses(BusID)
    )";
    $database->conn->exec($sql);
    echo "Table Routes created successfully <br />";
} catch(PDOException $e) {
    echo "Error creating Routes table: " . $e->getMessage() . "<br />";
}

// Create Bookings table
try {
    $sql = "
    CREATE TABLE IF NOT EXISTS Bookings (
        BookingID INT AUTO_INCREMENT PRIMARY KEY,
        UserID INT,
        RouteID INT,
        SeatNumber INT,
        PaymentStatus VARCHAR(50),
        FOREIGN KEY (UserID) REFERENCES Users(UserID),
        FOREIGN KEY (RouteID) REFERENCES Routes(RouteID)
    )";
    $database->conn->exec($sql);
    echo "Table Bookings created successfully <br />";
} catch(PDOException $e) {
    echo "Error creating Bookings table: " . $e->getMessage() . "<br />";
}

// Create UserBookings table
try {
    $sql = "
    CREATE TABLE IF NOT EXISTS UserBookings (
        UserID INT,
        BookingID INT,
        PRIMARY KEY (UserID, BookingID),
        FOREIGN KEY (UserID) REFERENCES Users(UserID),
        FOREIGN KEY (BookingID) REFERENCES Bookings(BookingID)
    )";
    $database->conn->exec($sql);
    echo "Table UserBookings created successfully <br />";
} catch(PDOException $e) {
    echo "Error creating UserBookings table: " . $e->getMessage() . "<br />";
}

// Create RouteBuses table
try {
    $sql = "
    CREATE TABLE IF NOT EXISTS RouteBuses (
        RouteID INT,
        BusID INT,
        PRIMARY KEY (RouteID, BusID),
        FOREIGN KEY (RouteID) REFERENCES Routes(RouteID),
        FOREIGN KEY (BusID) REFERENCES Buses(BusID)
    )";
    $database->conn->exec($sql);
    echo "Table RouteBuses created successfully <br />";
} catch(PDOException $e) {
    echo "Error creating RouteBuses table: " . $e->getMessage() . "<br />";
}

try {
    // Insert data into Users table
    $stmt = $database->conn->prepare("INSERT INTO Users (Username, Password, Name, Phone, Email) VALUES (?, ?, ?, ?, ?)");

    $users = [
        ['user1', password_hash('password1', PASSWORD_DEFAULT), 'User One', '1234567890', 'user1@example.com'],
        ['user2', password_hash('password2', PASSWORD_DEFAULT), 'User Two', '1234567891', 'user2@example.com'],
        ['user3', password_hash('password3', PASSWORD_DEFAULT), 'User Three', '1234567892', 'user3@example.com'],
        ['user4', password_hash('password4', PASSWORD_DEFAULT), 'User Four', '1234567893', 'user4@example.com'],
        ['user5', password_hash('password5', PASSWORD_DEFAULT), 'User Five', '1234567894', 'user5@example.com'],
        ['user6', password_hash('password6', PASSWORD_DEFAULT), 'User Six', '1234567895', 'user6@example.com'],
        ['user7', password_hash('password7', PASSWORD_DEFAULT), 'User Seven', '1234567896', 'user7@example.com'],
        ['user8', password_hash('password8', PASSWORD_DEFAULT), 'User Eight', '1234567897', 'user8@example.com'],
        ['user9', password_hash('password9', PASSWORD_DEFAULT), 'User Nine', '1234567898', 'user9@example.com'],
        ['user10', password_hash('password10', PASSWORD_DEFAULT), 'User Ten', '1234567899', 'user10@example.com']
    ];

    foreach ($users as $user) {
        $stmt->execute($user);
    }

    echo "Inserted 10 users into Users table successfully <br />";
} catch(PDOException $e) {
    echo "Error inserting data into Users table: " . $e->getMessage() . "<br />";
}

// Insert Data into the Buses table

try {
    // Insert data into Buses table
    $stmt = $database->conn->prepare("INSERT INTO Buses (PlateNumber) VALUES (?)");

    $buses = [
        ['ABC1234'],
        ['DEF5678'],
        ['GHI9012'],
        ['JKL3456'],
        ['MNO7890'],
        ['PQR1234'],
        ['STU5678'],
        ['VWX9012'],
        ['YZA3456'],
        ['BCD7890'],
        ['EFG1234'],
        ['HIJ5678'],
        ['KLM9012'],
        ['NOP3456'],
        ['QRS7890'],
        ['TUV1234'],
        ['WXY5678'],
        ['ZAB9012'],
        ['CDE3456'],
        ['FGH7890']
    ];

    foreach ($buses as $bus) {
        $stmt->execute($bus);
    }

    echo "Inserted 20 buses into Buses table successfully <br />";
} catch(PDOException $e) {
    echo "Error inserting data into Buses table: " . $e->getMessage() . "<br />";
}
// insert data into Routes table
try {
    // Insert data into Routes table
    $stmt = $database->conn->prepare("INSERT INTO Routes (Source, Destination, BusID) VALUES (?, ?, ?)");

    $routes = [
        ['Addis Ababa', 'Hossana', 1],
        ['Hossana', 'Addis Ababa', 2],
        ['Addis Ababa', 'Bahirdar', 3],
        ['Bahirdar', 'Addis Ababa', 4],
        ['Addis Ababa', 'Arbaminch', 5],
        ['Arbaminch', 'Addis Ababa', 6],
        ['Addis Ababa', 'Gonder', 7],
        ['Gonder', 'Addis Ababa', 8],
        ['Addis Ababa', 'Diredwa', 9],
        ['Diredwa', 'Addis Ababa', 10],
        ['Addis Ababa', 'Hossana', 11],
        ['Hossana', 'Addis Ababa', 12],
        ['Addis Ababa', 'Bahirdar', 13],
        ['Bahirdar', 'Addis Ababa', 14],
        ['Addis Ababa', 'Arbaminch', 15],
        ['Arbaminch', 'Addis Ababa', 16],
        ['Addis Ababa', 'Gonder', 17],
        ['Gonder', 'Addis Ababa', 18],
        ['Addis Ababa', 'Diredwa', 19],
        ['Diredwa', 'Addis Ababa', 20]
    ];

    foreach ($routes as $route) {
        $stmt->execute($route);
    }

    echo "Inserted 20 routes into Routes table successfully <br />";
} catch(PDOException $e) {
    echo "Error inserting data into Routes table: " . $e->getMessage() . "<br />";
}
// Insert into bookings table
try {
    // Insert data into Bookings table
    $stmt = $database->conn->prepare("INSERT INTO Bookings (UserID, RouteID, SeatNumber, PaymentStatus, DepartureDate) VALUES (?, ?, ?, ?, ?)");

    $bookings = [
        [1, 1, 1, 'Paid', '2024-05-20'],
        [2, 2, 2, 'Paid', '2024-05-21'],
        [3, 3, 3, 'Pending', '2024-05-22'],
        [4, 4, 4, 'Paid', '2024-05-23'],
        [5, 5, 5, 'Pending', '2024-05-24'],
        [6, 6, 6, 'Paid', '2024-05-25'],
        [7, 7, 7, 'Paid', '2024-05-26'],
        [8, 8, 8, 'Pending', '2024-05-27'],
        [9, 9, 9, 'Paid', '2024-05-28'],
        [10, 10, 10, 'Paid', '2024-05-29']
    ];

    foreach ($bookings as $booking) {
        $stmt->execute($booking);
    }

    echo "Inserted data into Bookings table successfully <br />";
} catch(PDOException $e) {
    echo "Error inserting data into Bookings table: " . $e->getMessage() . "<br />";
}
// Insert data into UserBookings and RouteBuses tables
    
try {
    // Insert data into UserBookings table
    $stmt = $database->conn->prepare("INSERT INTO UserBookings (UserID, BookingID) VALUES (?, ?)");

    $userBookings = [
        [1, 1],
        [2, 2],
        [3, 3],
        [4, 4],
        [5, 5],
        [6, 6],
        [7, 7],
        [8, 8],
        [9, 9],
        [10, 10]
    ];

    foreach ($userBookings as $userBooking) {
        $stmt->execute($userBooking);
    }

    echo "Inserted data into UserBookings table successfully <br />";

    // Insert data into RouteBuses table
    $stmt = $database->conn->prepare("INSERT INTO RouteBuses (RouteID, BusID) VALUES (?, ?)");

    $routeBuses = [
        [1, 1],
        [2, 2],
        [3, 3],
        [4, 4],
        [5, 5],
        [6, 6],
        [7, 7],
        [8, 8],
        [9, 9],
        [10, 10]
    ];

    foreach ($routeBuses as $routeBus) {
        $stmt->execute($routeBus);
    }

    echo "Inserted data into RouteBuses table successfully <br />";
} catch(PDOException $e) {
    echo "Error inserting data: " . $e->getMessage() . "<br />";
}
?>
