<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/popupMessage.css">
</head>
<body>
    <a href="login.php" class="aButton">Log In</a>
    <form method="post" id="registerForm">
        <input type="text" placeholder="Account Email" name="email">
        <button type="button" onclick="requestVerificationCode()">Request Verification Code</button>
        <input type="text" placeholder="Verification Code" name="code">

        <input type="text" placeholder="Your Nickname" name="nickname">
        <input type="password" placeholder="Account Password" name="password">
        <input type="password" placeholder="Confirm Password" name="confirmPassword">

        <button type="button" onclick="submitRegisterForm()">Register</button>
    </form>
    
    <script src="../utils/PopupMessages.js"></script>
    <script>
        const registerForm = document.getElementById('registerForm');

        async function requestVerificationCode() {
            const email = registerForm.email.value.trim();

            if (!email) {
                displayPopupMessage("Please enter your email first.");
                return;
            }

            try {
                const response = await fetch("../api/EmailVerification.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "email=" + encodeURIComponent(email)
                });

                const data = await response.text();
                displayPopupMessage(data);
            } 
            catch (err) {
                displayPopupMessage("Error requesting verification code.");
                console.error(err);
            }
        }

        async function submitRegisterForm() {
            const formData = new FormData(registerForm);

            if (formData.get("password").trim().length < 6) {
                displayPopupMessage("Password must contain at least 6 characters.");
                return;
            }

            if (formData.get("password").trim() !== formData.get("confirmPassword").trim()) {
                displayPopupMessage("Passwords do not match.");
                return;
            }

            try {
                const response = await fetch("../api/Register.php", {
                    method: "POST",
                    body: formData
                });

                const data = await response.text();
                displayPopupMessage(data);
            } 
            catch (err) {
                displayPopupMessage("Registration failed.");
                console.error(err);
            }
        }
    </script>
</body>
</html>