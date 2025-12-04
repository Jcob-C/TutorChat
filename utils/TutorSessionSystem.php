<?php
require_once __DIR__ . '/GeminiAPI.php';
require_once __DIR__ . '/PopupMessage/.php';

function startNewSession($topicTitle) {
    $_SESSION['ongoingTutorSession'] = [];
    $_SESSION['ongoingTutorSession']['topicTitle'] = $topicTitle;
    try {
        $_SESSION['ongoingTutorSession']['topicPlan'] = generateLessonPlan($topicTitle);
        header("Location: ../page/chat.php"); exit;
    }
    catch (Exception $e) {
        setPopupMessage("Failed to Generate a Lesson Plan");
        $_SESSION['ongoingTutorSession'] = [];
        return;
    }
}
?>