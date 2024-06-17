<?php
class Database {

    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname  = "busbookingdb";
    public $conn = null;

    public function __construct() {
        try {
            // Create a new PDO connection
            $this->conn = new PDO("mysql:host={$this->servername};dbname={$this->dbname}", $this->username, $this->password);
            
            // Set the PDO error mode to exception
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Connection successful message
            echo "";
        } catch(PDOException $e) {
            // Catch any connection errors and display the error message
            echo "Connection failed: " . $e->getMessage();
        }
    }
}
?>
