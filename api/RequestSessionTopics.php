<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../database/TutorSessions.php';

header('Content-Type: application/json');
session_start();
$conn = new mysqli(host, user, pass, db);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get parameters from GET request
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Number of topics per page

// Validate page number
if ($page < 1) {
    $page = 1;
}

try {
    // Get completed session topics from database
    $topics = getLastSessionByTopicSortedByScoreAsc($conn, $_SESSION['loggedinUserID'], $limit, $page);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'topics' => $topics,
        'page' => $page
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch completed topics'
    ]);
}

$conn->close();
?>