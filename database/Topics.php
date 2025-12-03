<?php
function getTopicTitle($conn, $topicID) {
    $stmt = $conn->prepare("SELECT title FROM topics WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $topicID);
    $stmt->execute();

    $title = null;
    $stmt->bind_result($title);
    $result = $stmt->fetch() ? $title : null;

    $stmt->close();
    return $result;
}

function getTopicSearch($conn, $keyword, $limit, $page) {
    $offset = ($page - 1) * $limit;
    $search = "%{$keyword}%";
    $stmt = $conn->prepare("SELECT * FROM topics WHERE title LIKE ? LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $search, $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();
    $topics = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    return $topics;
}

function getTopicsInRandomOrder($conn, $limit, $page) {
    $offset = ($page - 1) * $limit;
    $stmt = $conn->prepare("SELECT * FROM topics ORDER BY RAND() LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $topics = $result->fetch_all(MYSQLI_ASSOC);
    
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM topics");
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalCount = $countResult->fetch_assoc()['total'];
    
    $has_prev = $page > 1;
    $has_next = ($offset + $limit) < $totalCount;
    
    return [
        'topics' => $topics,
        'has_prev' => $has_prev,
        'has_next' => $has_next,
        'page' => $page
    ];
}
?>