<?php
require_once "../config/db.php";

function getConnection() {
    return new mysqli(host, user, pass, db);
}

function getUserID($email) {
    $db = getConnection();

    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);

    $stmt->execute();
    $stmt->bind_result($id);
    $result = $stmt->fetch() ? $id : null;

    return $result;
}

function getHashedPassword($email) {
    $db = getConnection();

    $stmt = $db->prepare("SELECT password FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);

    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $result = $stmt->fetch() ? $hashedPassword : null;

    return $result;
}

function getNickname($userID) {
    $db = getConnection();

    $stmt = $db->prepare("SELECT nickname FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userID);

    $stmt->execute();
    $stmt->bind_result($nickname);
    $result = $stmt->fetch() ? $nickname : null;

    return $result;
}

function getVerificationStatus($id) {
    $db = getConnection();

    $stmt = $db->prepare("SELECT verified FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);

    $stmt->execute();
    $stmt->bind_result($verified);
    $result = $stmt->fetch() ? $verified : null;

    return $result;
}

function setVerified($id) {
    $db = getConnection();
    $stmt = $db->prepare("UPDATE users SET verified = 1 WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
?>
