<?php
require_once '../utils/CleanerFunctions.php';
require_once '../utils/PageBlocker.php';
require_once '../utils/database/Users.php';

session_start();
loginBlock();
redirectAdmin();
checkPost();

function checkPost() {
    if (isset($_POST['logout'])) {
        resetSession();
        headTo('login.php');
    }
    if (isset($_POST['startSession'])) {
        $_SESSION['tutorSession'] = [];
        $_SESSION['tutorSession']['topicID'] = 1;
        headTo('pretest.php');
    }
    clearPost();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/popupMessage.css">
</head>
<body>
    <h1>User: <?= getNickname($_SESSION['userID']) ?></h1>
    <form method="post">
        <button type="submit" name="logout">Log Out</button>
    </form>  
    <form method="post">
        <button type="submit" name="startSession">Start Session with Topic 1</button>
    </form>  
</body>
</html>