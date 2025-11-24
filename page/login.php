<?php
require_once '../utils/CleanerFunctions.php';
require_once '../utils/PopupMessages.php';
require_once '../utils/Database.php';

session_start();
checkPost();
displayPopupMessage();

function checkPost() {
    if (isset($_POST['login'])) {
        login();
    }
    clearPost();
}

function login() {
    $cleanEmail = trim($_POST['email']);
    $cleanPassword = trim($_POST['password']);

    if (password_verify($cleanPassword, getHashedPassword($cleanEmail))) {
        $_SESSION['userID'] = getUserID($cleanEmail);
        header("Location: home.php");
        exit;
    }
    else {
        setNewPopupMessage("Invalid Login!");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/popupMessage.css">
</head>
<body>
    <form method="post">
        <input type="text" name="email" required>
        <input type="password" name="password" required>
        <button type="submit" name="login">Log In</button>
    </form>
    <a href="register/account.php">Create Account</a>
</body>
</html>