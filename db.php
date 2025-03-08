<?php
// Database credentials
$host = "localhost";      // Host name (usually 'localhost' when using XAMPP)
$username = "root";       // Default username in XAMPP
$password = "";           // Default password is empty in XAMPP
$dbname = "green_team"; // Replace with your actual database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
// Uncomment the next line to confirm connection
// echo "Connected successfully";

// Close the connection when done
// $conn->close();
?>