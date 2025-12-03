<?php
require_once __DIR__ . '/../database/Users.php';

function redirectUnauthorized($conn) {
    if (!isset($_SESSION['loggedinUserID']) || false == checkActivated($conn, $_SESSION['loggedinUserID'])) {
        $_SESSION = [];
        session_destroy();
        header('Location: ../page/login.php'); exit;
    }
}

function redirectAdmin() {
    if ($_SESSION['loggedinUserRole'] === 'admin') {
        header('Location: ../page/admin.php'); exit;
    }
}

function redirectLearner() {
    if ($_SESSION['loggedinUserRole'] === 'learner') {
        header('Location: ../page/learn.php'); exit;
    }
}
?>