<?php
require_once __DIR__ . '/../utils/CleanerFunctions.php';
require_once __DIR__ . '/../utils/PageBlocker.php';
require_once __DIR__ . '/../utils/database/TutorSessions.php';

session_start();
loginBlock();
redirectIfSkippedSessionProcedure('posttest');
redirectAdmin();
checkPost();

function checkPost() {
    if (isset($_POST['submitPosttest'])) {
        $total = 0;
        for ($i = 1; $i <= 4; $i++) {
            $question = "q" . $i;
            if (isset($_POST[$question])) {
                $total += intval($_POST[$question]);
            }
        }
        $_SESSION['tutorSession']['postscore'] = $total;
        saveNewSession(
            $_SESSION['userID'], 
            $_SESSION['tutorSession']['topicID'],
            $_SESSION['tutorSession']['prescore'],
            $_SESSION['tutorSession']['postscore'],
            $_SESSION['tutorSession']['messages'],
            $_SESSION['tutorSession']['summary']
        );
        unset($_SESSION['tutorSession']);
        headTo('conclusion.php');
    }
    clearPost();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Theme and custom CSS -->
    <link rel="stylesheet" href="../assets/theme.css">
    <link rel="stylesheet" href="../assets/popupMessage.css">

    <title>TutorChat Post-test</title>
</head>
<body>
   <div class="container py-5">
    <!-- Branding -->
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-white">
            <i class="bi bi-chat-dots-fill text-brand"></i> TutorChat
        </h1>
    </div>

    <!-- Centered Narrow Form Card -->
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="card shadow-lg border-0 rounded-3 bg-white">
                <div class="card-body p-5">
                    <h2 class="card-title fw-bold mb-4 text-center text-dark">Post-test Survey</h2>

                    <form action="" method="post">
                        <!-- Question 1 -->
                        <div class="mb-4">
                            <p class="form-label fw-normal text-dark">1. I feel confident explaining the main ideas of this topic.</p>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q1" value="1" id="q1-1" required>
                                    <label class="form-check-label text-dark" for="q1-1">1</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q1" value="2" id="q1-2">
                                    <label class="form-check-label text-dark" for="q1-2">2</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q1" value="3" id="q1-3">
                                    <label class="form-check-label text-dark" for="q1-3">3</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q1" value="4" id="q1-4">
                                    <label class="form-check-label text-dark" for="q1-4">4</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q1" value="5" id="q1-5">
                                    <label class="form-check-label text-dark" for="q1-5">5</label>
                                </div>
                            </div>
                        </div>

                        <!-- Question 2 -->
                        <div class="mb-4">
                            <p class="form-label fw-normal text-dark">2. I understand the key terms and concepts related to this topic.</p>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q2" value="1" id="q2-1" required>
                                    <label class="form-check-label text-dark" for="q2-1">1</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q2" value="2" id="q2-2">
                                    <label class="form-check-label text-dark" for="q2-2">2</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q2" value="3" id="q2-3">
                                    <label class="form-check-label text-dark" for="q2-3">3</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q2" value="4" id="q2-4">
                                    <label class="form-check-label text-dark" for="q2-4">4</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q2" value="5" id="q2-5">
                                    <label class="form-check-label text-dark" for="q2-5">5</label>
                                </div>
                            </div>
                        </div>

                        <!-- Question 3 -->
                        <div class="mb-4">
                            <p class="form-label fw-normal text-dark">3. I could apply the knowledge from this topic in practice.</p>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q3" value="1" id="q3-1" required>
                                    <label class="form-check-label text-dark" for="q3-1">1</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q3" value="2" id="q3-2">
                                    <label class="form-check-label text-dark" for="q3-2">2</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q3" value="3" id="q3-3">
                                    <label class="form-check-label text-dark" for="q3-3">3</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q3" value="4" id="q3-4">
                                    <label class="form-check-label text-dark" for="q3-4">4</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q3" value="5" id="q3-5">
                                    <label class="form-check-label text-dark" for="q3-5">5</label>
                                </div>
                            </div>
                        </div>

                        <!-- Question 4 -->
                        <div class="mb-4">
                            <p class="form-label fw-normal text-dark">4. I am motivated to learn more about this topic.</p>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q4" value="1" id="q4-1" required>
                                    <label class="form-check-label text-dark" for="q4-1">1</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q4" value="2" id="q4-2">
                                    <label class="form-check-label text-dark" for="q4-2">2</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q4" value="3" id="q4-3">
                                    <label class="form-check-label text-dark" for="q4-3">3</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q4" value="4" id="q4-4">
                                    <label class="form-check-label text-dark" for="q4-4">4</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="q4" value="5" id="q4-5">
                                    <label class="form-check-label text-dark" for="q4-5">5</label>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-5 d-flex justify-content-center gap-3">
                            <button type="submit" name="submitPosttest" class="btn btn-brand btn-lg fw-semibold">Save Session</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <small class="text-white-50">© <?= date('Y'); ?> TutorChat - Your AI Learning Companion</small>
    </div>
</div>


    <!-- Bootstrap JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>