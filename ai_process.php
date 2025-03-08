<?php
// ai_process.php

header('Content-Type: application/json');

// Database credentials
$host = "localhost";      // Host name (usually 'localhost' when using XAMPP)
$username = "root";       // Default username in XAMPP
$password = "";           // Default password is empty in XAMPP
$dbname = "green_team";   // Replace with your actual database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Get the user prompt from the POST request
$data = json_decode(file_get_contents("php://input"));
$prompt = $data->prompt ?? '';

if (empty($prompt)) {
    echo json_encode(["status" => "error", "message" => "Prompt is required"]);
    exit();
}

// API Key for Gemini (replace with your actual API key)
$apiKey = 'YOUR_GEMINI_API_KEY';
$geminiModel = 'gemini-2.0-flash'; // Model you want to use

// The table schema for MySQL (Users Table)
$tableSchema = "
Users Table (Volunteers, Participants, Staff):
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    user_type ENUM('volunteer', 'participant', 'staff') NOT NULL,
    join_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'active'
);
";

// Prepare the API request payload
$requestPayload = json_encode([
    "contents" => [
        [
            "parts" => [
                [
                    "text" => "Given the following database schema: {$tableSchema}\nConvert this request into a MySQL query: \"$prompt\". Output only the raw SQL query without any code formatting like ```sql or ```."
                ]
            ]
        ]
    ]
]);

// Call the Gemini API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://generativelanguage.googleapis.com/v1/models/{$geminiModel}:generateContent?key={$apiKey}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $requestPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);

if ($response === false) {
    echo json_encode(["status" => "error", "message" => "Failed to call Gemini API"]);
    exit();
}

curl_close($ch);

// Decode the Gemini API response
$responseData = json_decode($response, true);

// Check if the response contains the generated SQL query
if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode(["status" => "error", "message" => "No SQL query generated"]);
    exit();
}

// Extract and clean the SQL query
$sqlQuery = trim($responseData['candidates'][0]['content']['parts'][0]['text']);
$sqlQuery = preg_replace('/```sql|```/', '', $sqlQuery); // Remove markdown formatting

// Execute the generated SQL query
if (str_starts_with(strtolower($sqlQuery), "select")) {
    // SELECT Queries: Fetch and return data
    $result = $conn->query($sqlQuery);
    if ($result) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(["status" => "success", "sqlQuery" => $sqlQuery, "data" => $data]);
    } else {
        echo json_encode(["status" => "error", "message" => "Query execution failed: " . $conn->error]);
    }
} else {
    // INSERT, UPDATE, DELETE Queries: Execute and confirm
    if ($conn->query($sqlQuery) === TRUE) {
        echo json_encode(["status" => "success", "sqlQuery" => $sqlQuery, "message" => "Query executed successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Query execution failed: " . $conn->error]);
    }
}

// Close database connection
$conn->close();
?>
