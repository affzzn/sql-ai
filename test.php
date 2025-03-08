<?php
include('db.php');

// Test the connection
$sql = "SELECT * FROM your_table"; // Replace with a table in your database
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        echo "id: " . $row["id"] . " - Name: " . $row["name"] . "<br>"; // Replace with your column names
    }
} else {
    echo "0 results";
}

$conn->close();
?>