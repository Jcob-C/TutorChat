<?php
require_once '../utils/database/Users.php';
require_once '../utils/database/VerificationCodes.php';

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$nickname = isset($_POST['nickname']) ? trim($_POST['nickname']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$code = isset($_POST['code']) ? trim($_POST['code']) : '';

if (empty($email) || empty($nickname) || empty($password) || empty($code)) {
    echo "All fields are required.";
    exit;
}

$userID = getUserID($email);
if ($userID) {
    echo "Email is already registered.";
    exit;
}

$storedCode = getVerificationCode($email);
if (!$storedCode) {
    echo "No valid verification code found or it has expired.";
    exit;
}

if ($storedCode != $code) {
    echo "Incorrect verification code.";
    exit;
}

if (createUser($email, $nickname, $password)) {
    echo "Registration successful! You can now log in.";
} 
else {
    echo "Registration failed. Please try again.";
}
?>
