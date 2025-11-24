<?php
require_once '../utils/CleanerFunctions.php';
require_once '../utils/PageBlocker.php';

session_start();
checkUserID();
checkPost();

function checkPost() {
    if (isset($_POST['logout'])) {
        logout();
    }
    clearPost();
}

function logout() {
    $_SESSION = [];
    session_destroy();
    headTo("login.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="post">
        <button type="submit" name="logout">Log Out</button>
    </form>  
</body>
</html>