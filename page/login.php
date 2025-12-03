<?php
require_once __DIR__ . '/../utils/popupmessage/.php';
require_once __DIR__ . '/../database/Users.php';
require_once __DIR__ . '/../config/db.php';

$conn = new mysqli(host, user, pass, db);
session_start();
checkPost();
displayPopupMessage();

function checkPost() {
    if (isset($_POST['login'])) {
        login();
        header("Location: ".$_SERVER['PHP_SELF']); exit;
    }
}

function login() {
    global $conn;
    $cleanEmail = trim($_POST['email']);
    $cleanPassword = trim($_POST['password']);
    $_SESSION['loginEmailInput'] = $cleanEmail;

    if (password_verify($cleanPassword, getHashedPasswordByEmail($conn, $cleanEmail))) {
        $_SESSION = [];
        $_SESSION['loggedinUserID'] = getUserID($conn, $cleanEmail);
        $_SESSION['loggedinUserRole'] = getUserRole($conn, $_SESSION['loggedinUserID']);

        if (!checkActivated($conn, $_SESSION['loggedinUserID'])) {
            setPopupMessage("Deactivated Account");
            return;
        }
        if ($_SESSION['loggedinUserRole'] === 'admin') {
            header("Location: admin.php"); exit;
        }
        else {
            header("Location: learn.php"); exit;
        }
    }
    else {
        setPopupMessage("Invalid Login");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TutorChat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/Style.css">
    <link rel="stylesheet" href="../assets/PopupMessage.css">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row min-vh-100 justify-content-center">
            <div class="col-12 col-md-8 col-lg-9 d-flex justify-content-center align-items-center bg-light order-1 order-md-1 mb-4 mb-md-0">
                <div class="text-center px-4">
                    <br>
                    <h1>Welcome to <i class="bi bi-chat-dots-fill icon-primary"></i> TutorChat</h1>
                    <p class="lead">Your personal tutoring assistant, available 24/7.</p>
                </div>
            </div>
            <div class="col-12 col-md-4 col-lg-3 bg-white shadow d-flex flex-column justify-content-center p-4 order-2 order-md-2">
                <h2 class="mb-4 d-flex align-items-center gap-2 justify-content-center">Log In</h2>
                <form method="post">
                    <label for="email" class="form-label">Email</label>
                    <div class="mb-3">
                        <input name="email" type="text" id="email" class="form-control" placeholder="Enter your email" 
                            value="<?= isset($_SESSION['loginEmailInput']) ? $_SESSION['loginEmailInput'] : '' ?>" required
                        >
                    </div>
                    <label for="password" class="form-label">Password</label>
                    <div class="mb-3 position-relative d-flex align-items-center">
                        <input name="password" type="password" id="password" class="form-control me-2" placeholder="Enter your password" required>
                        <button type="button" id="togglePassword" class="btn btn-outline-secondary">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                    <br>
                    <button name="login" type="submit" class="btn btn-primary w-100">Log In</button>
                </form>
                <p class="mt-3 text-center">
                    Don't have an account? 
                    <a href="register.php" class="fw-bold">Register</a>
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script> 
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', () => {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            togglePassword.innerHTML = type === 'password' ? '<i class="bi bi-eye-fill"></i>' : '<i class="bi bi-eye-slash-fill"></i>';
        });
    </script>
</body>
</html>