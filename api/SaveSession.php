<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../database/TutorSessions.php';

session_start();
$jsonTranscript = file_get_contents("php://input");
$conn = new mysqli(host,user,pass,db);

if ($jsonTranscript) {
    try {
        $_SESSION['ongoingTutorSession']['id'] = saveNewSession($conn, $_SESSION['loggedinUserID'], $_SESSION['ongoingTutorSession']['topicTitle'], $jsonTranscript);
        echo "Saved.";
    }
    catch (Exception $e) {
        echo $e->getMessage();
    }
} 
else {
    echo "No data received.";
}
?>