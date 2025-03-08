<?php
// search_volunteers.php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

include('db_connection.php');

try {
    $searchTerm = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
    
    $conditions = [];
    $params = [];
    
    if ($searchTerm) {
        $conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ? )";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
        $params[] = "%$searchTerm%";
    }
    
    if ($status) {
        $conditions[] = "status = ?";
        $params[] = $status;
    }
    
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
    
    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $volunteers = array();
    while ($row = $result->fetch_assoc()) {
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