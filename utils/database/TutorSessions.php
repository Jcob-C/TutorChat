<?php
require_once __DIR__ . '/../../config/db.php';

function saveNewSession($userID, $topicID, $preScore, $postScore, $jsonMessages) {
    $db = getConnection();

    $stmt = $db->prepare("INSERT INTO tutor_sessions (user_id, topic_id, pre_score, post_score, messages) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiis", $userID, $topicID, $preScore, $postScore, $jsonMessages);

    return $stmt->execute();
}

function getLatestUserSessions($userID, $limit, $page) {
    $db = getConnection();

    $offset = ($page - 1) * $limit;
    $stmt = $db->prepare("SELECT * FROM tutor_sessions WHERE user_id = ? ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("iii", $userID, $limit, $offset);

    $stmt->execute();
    $result = $stmt->get_result();
    $resultArray = $result->fetch_all(MYSQLI_ASSOC);

    return $resultArray;
}

// returns topic ids that user had a session with but still lacks confidence or understanding on
function getPreviousTopicIDsSortedByPostScore($userID, $limit, $page) {
    $db = getConnection();
    $offset = ($page - 1) * $limit;

    $sql = "
        SELECT ts.topic_id
        FROM tutor_sessions ts
        INNER JOIN (
            SELECT topic_id, MAX(id) AS latest_id
            FROM tutor_sessions
            WHERE user_id = ?
            GROUP BY topic_id
        ) latest
        ON ts.id = latest.latest_id
        ORDER BY ts.post_score ASC
        LIMIT ? OFFSET ?
    ";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("iii", $userID, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $topicIDs = [];
    while ($row = $result->fetch_assoc()) {
        $topicIDs[] = $row['topic_id'];
    }

    return $topicIDs;
}

?>