<?php
// ai_process.php

header('Content-Type: application/json');

// Database credentials
$host = "localhost";
$username = "root";
$password = "";
$dbname = "green_team"; 

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
$geminiModel = 'gemini-2.0-flash';

// Full database schema including constraints
$databaseSchema = "
DATABASE: green_team

-- Users Table (Volunteers, Participants, Staff)
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

-- Programs Table (Green Team Programs)
CREATE TABLE programs (
    program_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    age_group VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Events Table (Events for Programs)
CREATE TABLE events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    program_id INT,
    event_name VARCHAR(100) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    location VARCHAR(255),
    max_capacity INT DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0.00,
    payment_required BOOLEAN DEFAULT FALSE,
    status ENUM('upcoming', 'completed', 'cancelled') DEFAULT 'upcoming',
    FOREIGN KEY (program_id) REFERENCES programs(program_id) ON DELETE SET NULL
);

-- Event Participants Table (Track Event Attendance)
CREATE TABLE event_participants (
    participant_id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('pending', 'paid', 'waived') DEFAULT 'pending',
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Volunteer Skills Table
CREATE TABLE volunteer_skills (
    skill_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Feedback Table (Track Feedback & Ratings)
CREATE TABLE feedback (
    feedback_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    event_id INT,
    comments TEXT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    submitted_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE SET NULL
);

-- Donations Table (Financial Contributions)
CREATE TABLE donations (
    donation_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    amount DECIMAL(10,2) NOT NULL,
    donation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Audit Log Table (Tracks System Changes)
CREATE TABLE audit_log (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action_type VARCHAR(100) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
);

-- User Authentication Table (Login System)
CREATE TABLE user_authentication (
    auth_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Other tables omitted for brevity. They follow the same constraint handling.

-- Based on the schema above, generate a MySQL query for the following request:
\"$prompt\"
Ensure that the response contains only the raw SQL query without any formatting, markdown, or extra text.
";

// Prepare the API request payload
$requestPayload = json_encode([
    "contents" => [
        [
            "parts" => [
                ["text" => $databaseSchema]
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
