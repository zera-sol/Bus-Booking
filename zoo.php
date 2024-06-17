<?php   
$conn = mysqli_connect("localhost", "root", "", "yodahe") or die("failed to connect".mysqli_error())."<br>";
echo "connected sucessfully <br>";
$sql ="DROP database yodahe";
mysqli_query($conn, $sql) or die("failed to insert". mysqli_error(). "<br>");
echo "inserted successfully!";


?>