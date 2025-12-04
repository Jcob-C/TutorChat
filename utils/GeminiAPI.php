<?php
require_once __DIR__ . '/../config/ai.php';

function generateLessonPlan($topic) {
    return generateText("
        Your response should be **HTML-formatted**. Please **do not use markdown formatting** or code block markers (like \`\`\`html\`\`\` or \`\`\`). Instead, directly return HTML tags such as <h1>, <h2>, <ul>, <li>, <p>, <strong>, and <em> to structure your answers clearly and make the content easy to read.
        Create a structured lesson plan for the topic '$topic' designed to guide an engaging and interactive discussion. The lesson plan should include the following sections:
        1. Introduction: Start with a brief overview of the topic, including key objectives and the main points to be covered. This should set the stage for discussion and invite participation from the audience.
        2. Discussion Section 1: [Title of the section]: Introduce the first key concept. Provide background information, and present relevant examples or thought-provoking questions to stimulate conversation.
        3. Discussion Section 2: [Title of the section]: Introduce the second concept or idea. Include questions or interactive activities to encourage further exploration of the topic. Consider using case studies or real-life examples to spark discussion.
        4. Discussion Section 3: [Title of the section]: Present additional key concepts or perspectives. Encourage the audience to share their thoughts, insights, or experiences related to the topic.
        5. Conclusion: Summarize the key takeaways from the discussion, emphasizing important points and providing a space for final questions or reflections.
        The goal is to foster an open, engaging conversation where participants feel comfortable contributing ideas and questions. Please ensure that the lesson plan encourages active participation and makes room for thoughtful dialogue.
    ");
}


function generateText($input) {
    $model  = 'models/gemini-2.0-flash-lite';
    $url = "https://generativelanguage.googleapis.com/v1beta/$model:generateContent?key=" . urlencode(geminiAPIKey);
    $data = [
        "contents" => [         
            [
                "parts" => [   
                    ["text" => $input]
                ]
            ]
        ],
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_POST, true); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch); 
    if (curl_errno($ch)) {
        echo "Curl error: " . curl_error($ch);
    }

    return json_decode($response, true)['candidates'][0]['content']['parts'][0]['text'];
}
?>