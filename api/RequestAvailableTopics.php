<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../database/Topics.php';

header('Content-Type: application/json');

$conn = new mysqli(host, user, pass, db);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get parameters from GET request
$keyword = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Number of topics per page

// Validate page number
if ($page < 1) {
    $page = 1;
}

try {
    // Get topics from database
    $topics = getTopicSearch($conn, $keyword, $limit, $page);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'topics' => $topics,
        'page' => $page,
        'search' => $keyword
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch topics'
    ]);
}

$conn->close();
?>