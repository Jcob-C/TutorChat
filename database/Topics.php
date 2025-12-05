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
    $stmt = $conn->prepare("SELECT * FROM topics WHERE title LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $search, $limit, $offset);
    $stmt->execute();

    $result = $stmt->get_result();
    $topics = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    return $topics;
}
?>