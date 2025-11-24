<?php
function headTo($destination) {
    header("Location: $destination");
    exit;
}

function clearPost() {
    if (!empty($_POST)) {
        headto($_SERVER['PHP_SELF']);
        exit;
    }
}

function cleanHTML($text) {
    return htmlspecialchars(trim($text), ENT_QUOTES, 'UTF-8');
}
?>