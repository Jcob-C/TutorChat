<?php
function startNewSession($topicTitle) {
    $_SESSION['ongoingTutorSession'] = [];
    $_SESSION['ongoingTutorSession']['topicTitle'] = $topicTitle;
    header("Location: ../page/chat.php"); exit;
}
?>