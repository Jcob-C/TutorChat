<?php
function checkUserID() {
    if (!isset($_SESSION['userID'])) { // add database check
        header("Location: login.php");
        exit;
    }
}

function checkUserRole() {
    
}

function checkUserVerification() {

}

function checkLastSession() {
    
}
?>