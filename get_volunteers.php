<?php
// get_volunteers.php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

include('db_connection.php');

try {
    $sql = "SELECT 
        user_id,
        first_name,
        last_name,
        email,
        phone,
        date_of_birth,
        join_date,
        status 
    FROM users 
    WHERE user_type = 'volunteer'";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $volunteers = array();
    while ($row = $result->fetch_assoc()) {
        // Format dates for consistency
        $row['date_of_birth'] = $row['date_of_birth'] ? date('Y-m-d', strtotime($row['date_of_birth'])) : null;
        $row['join_date'] = date('Y-m-d H:i:s', strtotime($row['join_date']));
        $volunteers[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $volunteers
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>