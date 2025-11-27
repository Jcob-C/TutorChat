<?php
require_once __DIR__ . '/../utils/CleanerFunctions.php';
require_once __DIR__ . '/../utils/PageBlocker.php';

session_start();
loginBlock();
redirectIfSkippedSessionProcedure('pretest');
redirectAdmin();
checkPost();

function checkPost() {
    if (isset($_POST['submitPretest'])) {
        $total = 0;
        for ($i = 1; $i <= 4; $i++) {
            $question = "q" . $i;
            if (isset($_POST[$question])) {
                $total += intval($_POST[$question]);
            }
        }
        $_SESSION['tutorSession']['prescore'] = $total;
        headTo('chat.php');
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
    <form action="" method="post">
        <p>1. I feel confident explaining the main ideas of this topic.</p>
        <label>1 <input type="radio" name="q1" value="1" required></label>
        <label>2 <input type="radio" name="q1" value="2"></label>
        <label>3 <input type="radio" name="q1" value="3"></label>
        <label>4 <input type="radio" name="q1" value="4"></label>
        <label>5 <input type="radio" name="q1" value="5"></label>

        <p>2. I understand the key terms and concepts related to this topic.</p>
        <label>1 <input type="radio" name="q2" value="1" required></label>
        <label>2 <input type="radio" name="q2" value="2"></label>
        <label>3 <input type="radio" name="q2" value="3"></label>
        <label>4 <input type="radio" name="q2" value="4"></label>
        <label>5 <input type="radio" name="q2" value="5"></label>

        <p>3. I could apply the knowledge from this topic in practice.</p>
        <label>1 <input type="radio" name="q3" value="1" required></label>
        <label>2 <input type="radio" name="q3" value="2"></label>
        <label>3 <input type="radio" name="q3" value="3"></label>
        <label>4 <input type="radio" name="q3" value="4"></label>
        <label>5 <input type="radio" name="q3" value="5"></label>

        <p>4. I am motivated to learn more about this topic.</p>
        <label>1 <input type="radio" name="q4" value="1" required></label>
        <label>2 <input type="radio" name="q4" value="2"></label>
        <label>3 <input type="radio" name="q4" value="3"></label>
        <label>4 <input type="radio" name="q4" value="4"></label>
        <label>5 <input type="radio" name="q4" value="5"></label>

        <br>
        <button type="submit" name="submitPretest">Proceed to Chat</button>
    </form>
</body>
</html>