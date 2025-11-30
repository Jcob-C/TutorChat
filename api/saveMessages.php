<?php
require_once __DIR__ . '/../utils/GeminiAPI.php';

session_start();

$data = file_get_contents("php://input");

if ($data) {
    $_SESSION['tutorSession']['messages'] = json_decode($data, true);

    // Build conversation text
    $messages = $_SESSION['tutorSession']['messages'];
    $conversationText = "";

    foreach ($messages as $msg) {
        $speaker = $msg["role"] === "user" ? "User" : "Assistant";
        $conversationText .= "$speaker: " . $msg["message"] . "\n";
    }

    // 3 to 5 sentence summary prompt
    $prompt = "
    Summarize the following conversation in **3 to 5 sentences**.
    Capture the main points, goals, and responses without unnecessary details.

    Conversation:
    $conversationText
    ";

    // Generate summary
    $_SESSION['tutorSession']['summary'] = generateText($prompt);

    echo "saved";
} 
else {
    echo "No data received.";
}

?>