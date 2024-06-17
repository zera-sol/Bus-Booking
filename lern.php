<?php 
 $conn = mysqli_connect("localhost", "root", "", "zera");
 if(!$conn){
    echo "Connection failed ". mysqli_error();
 }
 else echo "connected sucessfully";
 $sql = "CREATE TABLE user (userId INT(6) AUTO_INCREMENT PRIMARY KEY,  userName varchar(50), age INT(3), email varchar(50), password varchar(50))";

 if(mysqli_query($conn, $sql)){
    echo "table created successfully";
 }else{
    echo "Failed to create a table! ". mysqli_error();
 }
?>