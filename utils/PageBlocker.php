<?php
require_once 'CleanerFunctions.php';
require_once 'database/Users.php';

function loginBlock() {
    if (!isset($_SESSION['userID']) || false == checkActivated($_SESSION['userID'])) {
        resetSession();
        headTo('../page/login.php');
    }
}

function redirectAdmin() {
    if (getUserRole($_SESSION['userID']) === 'admin') {
        headTo('../page/admin.php');
    }
}

function redirectLearner() {
    if (getUserRole($_SESSION['userID']) === 'learner') {
        headTo('../page/home.php');
    }
}

function redirectIfSkippedSessionProcedure($page) {
    $topicSet = isset($_SESSION['tutorSession']['topicID']);
    $prescoreSet = isset($_SESSION['tutorSession']['prescore']);
    $messagesSet = isset($_SESSION['tutorSession']['messages']);

    if ((!$topicSet && $page === 'pretest') || ((!$topicSet || !$prescoreSet) && $page === 'chat') || ((!$topicSet || !$prescoreSet || !$messagesSet) && $page === 'posttest')) {
        unset($_SESSION['tutorSession']);
        headTo('../page/home.php');
    }
}
?>