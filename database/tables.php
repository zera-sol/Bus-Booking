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

?>
