<?php
require_once '../utils/CleanerFunctions.php';
require_once '../utils/PageBlocker.php';

session_start();
loginBlock();
redirectLearner();
checkPost();

function checkPost() {
    if (isset($_POST['logout'])) {
        resetSession();
        headTo('login.php');
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
    <h1>ADMIN</h1>
    <form method="post">
        <button type="submit" name="logout">Log Out</button>
    </form> 
</body>
</html>