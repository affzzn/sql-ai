<?php
// get_volunteer.php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

include('db_connection.php');

try {
    if (!isset($_GET['user_id'])) {
        throw new Exception("Missing user_id parameter");
    }
    
    $user_id = intval($_GET['user_id']);
    
    $query = "SELECT 
        user_id, 
        first_name, 
        last_name, 
        email, 
        phone, 
        date_of_birth, 
        join_date, 
        status 
    FROM users 
    WHERE user_id = ? AND user_type = 'volunteer'";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $volunteer = $result->fetch_assoc();
        $volunteer['date_of_birth'] = $volunteer['date_of_birth'] ? date('Y-m-d', strtotime($volunteer['date_of_birth'])) : null;
        $volunteer['join_date'] = date('Y-m-d H:i:s', strtotime($volunteer['join_date']));
        
        echo json_encode(['status' => 'success', 'volunteer' => $volunteer]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Volunteer not found']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>