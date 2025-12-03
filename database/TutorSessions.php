<?php
function saveNewSession($conn, $userID, $topicTitle, $quizScore, $jsonTranscript) {
    $stmt = $conn->prepare("INSERT INTO tutor_sessions (user_id, topic_title, quiz_score, transcript) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $userID, $topicTitle, $quizScore, $jsonTranscript);
    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        return $conn->insert_id;
    } else {
        return 0;
    }
}

function getSession($conn,  $id) {
    $stmt = $conn->prepare("SELECT * FROM tutor_sessions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $session = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close;
    return $session;
}

function getLastSessionByTopicSortedByScoreAsc($conn, $id, $limit, $page) {
    $offset = ($page - 1) * $limit;
    $stmt = $conn->prepare("
        SELECT s1.topic_title, s1.quiz_score 
        FROM tutor_sessions s1
        INNER JOIN (
            SELECT topic_title, MAX(concluded) as last_concluded
            FROM tutor_sessions
            WHERE user_id = ?
            GROUP BY topic_title
        ) s2 ON s1.topic_title = s2.topic_title AND s1.concluded = s2.last_concluded
        WHERE s1.user_id = ?
        ORDER BY s1.quiz_score ASC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("iiii", $id, $id, $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();
    $sessions = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    return $sessions;
}

function getLatestUserSessions($conn, $userID, $limit, $page) {
    $offset = ($page - 1) * $limit;
    $stmt = $conn->prepare("
        SELECT 
            tutor_sessions.*, 
            topics.title as topic_title 
        FROM tutor_sessions 
        JOIN topics ON tutor_sessions.topic_id = topics.id 
        WHERE tutor_sessions.user_id = ? 
        ORDER BY tutor_sessions.id DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("iii", $userID, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $sessions = $result->fetch_all(MYSQLI_ASSOC);
    
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM tutor_sessions WHERE user_id = ?");
    $countStmt->bind_param("i", $userID);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalCount = $countResult->fetch_assoc()['total'];
    
    $has_prev = $page > 1;
    $has_next = ($offset + $limit) < $totalCount;
    
    return [
        'sessions' => $sessions,
        'has_prev' => $has_prev,
        'has_next' => $has_next,
        'page' => $page
    ];
}
?>