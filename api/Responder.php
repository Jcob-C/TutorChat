<?php
require_once __DIR__ . '/../utils/GeminiAPI.php';

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$chatHistory = $data['chatHistory'] ?? [];
$newMessage  = $data['newMessage'] ?? '';
$topicTitle  = $data['topicTitle'] ?? 'Unknown Topic';

// Build strict topic-focused prompt
$prompt = "You are an AI tutor. The conversation topic is: \"$topicTitle\".\n";
$prompt .= "You MUST answer ONLY within this topic.\n";
$prompt .= "If the user asks something unrelated, politely redirect them back to \"$topicTitle\".\n\n";

$prompt .= "Conversation so far:\n";

foreach ($chatHistory as $entry) {
    $role = $entry['role'] === 'user' ? 'User' : 'Assistant';
    $prompt .= "$role: " . $entry['message'] . "\n";
}

$prompt .= "\nUser: $newMessage\nAssistant:";

// Generate response
try {
    echo generateText($prompt);
} 
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
