<?php
require_once __DIR__ . '/../utils/GeminiAPI.php';

session_start();
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$chatHistory = $data['history'] ?? [];
$topicTitle = $_SESSION['ongoingTutorSession']['topicTitle'];
$lessonPlan = $_SESSION['ongoingTutorSession']['topicPlan'];

$prompt = "
You are an AI tutor. The conversation topic is: \"$topicTitle\".
The lesson plan for this topic is as follows: $lessonPlan.

You MUST answer ONLY within this topic.
If the user asks something unrelated, politely redirect them back to the topic: \"$topicTitle\".
If the user says \"proceed\", \"proceed with the lesson\", \"let's move on\", \"next\", \"continue\", or any similar phrases indicating they want to move to the next section, transition to the next section of the lesson plan, starting with the **Introduction**.

Your response should be **HTML-formatted**. Please **do not use markdown formatting** or code block markers (like \`\`\`html\`\`\` or \`\`\`). Instead, directly return HTML tags such as <h1>, <h2>, <ul>, <li>, <p>, <strong>, and <em> to structure your answers clearly and make the content easy to read.

If possible, end your response with possible questions the user can ask regarding the topic.
Be simple with words and try to keep the conversation going.

Conversation so far:
";

foreach ($chatHistory as $entry) {
    $role = $entry['role'] === 'user' ? 'User' : 'Assistant';
    $content = $entry['content'] ?? '';
    $prompt .= "$role: $content\n";
}

$prompt .= "\nAssistant:";

try {
    echo generateText($prompt);
} 
catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}