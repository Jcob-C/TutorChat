<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/PageBlocker.php';
require_once __DIR__ . '/../utils/TutorSessionSystem.php';
require_once __DIR__ . '/../utils/PopupMessage/.php';

$conn = new mysqli(host, user, pass, db);
session_start();
redirectUnauthorized($conn);
redirectAdmin();
displayPopupMessage();

if (isset($_POST['startSession'])) {
    $topic = trim(isset($_POST['topicTitle']) ? $_POST['topicTitle'] : $_POST['startSession']);
    if ($topic != '') {
        startNewSession($title);
    }
    else {
        setPopupMessage("Please enter a topic.");
        header("Location: ".$_SERVER['PHP_SELF']); exit;
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/Style.css">
    <link rel="stylesheet" href="../assets/PopupMessage.css">
</head>
<body>
    <header class="bg-white py-3 mb-4 shadow-sm">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <h1 class="h3 mb-0 text-nowrap"><i class="bi bi-chat-dots-fill icon-primary"></i> TutorChat</h1>
                <nav class="d-flex flex-wrap gap-3 align-items-center">
                    <a href="learn.php" class="text-decoration-none fw-bold"><i class="bi bi-book me-1"></i>Learn</a>
                    <a href="analytics.php" class="text-decoration-none"><i class="bi bi-speedometer2 me-1"></i>Analytics</a>
                    <a href="settings.php" class="text-decoration-none"><i class="bi bi-person-circle me-1"></i>Settings</a>
                </nav>
            </div>
        </div>
    </header>   
    <div class="container-fluid px-4">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-warning bg-opacity-10 border-0">
                        <h2 class="h5 mb-0"><i class="bi bi-lightbulb text-warning me-2"></i>Learn Something New</h2>
                    </div>
                    <div class="card-body">
                        <div class="input-group input-group-sm mb-3">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search topics...">
                            <button class="btn btn-primary" id="searchBtn"><i class="bi bi-search"></i></button>
                        </div>
                        <div id="learnnewContainer" class="list-group list-group-flush mb-3">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item px-0 border-primary">
                            <div class="d-flex gap-2">
                                <form method="post" class="w-100 d-flex gap-2">
                                    <input type="text" name="topicTitle" class="form-control form-control-sm" placeholder="Can't find what you're looking for?" required>
                                    <button type="submit" name="startSession" class="btn btn-outline-primary btn-sm text-nowrap flex-shrink-0"><i class="bi bi-chat-dots me-1"></i>Chat</button>
                                </form>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button class="btn btn-outline-secondary btn-sm" id="learnnewPrevBtn"><i class="bi bi-chevron-left"></i> Prev</button>
                            <small class="text-muted">Page <span id="learnnewPageNum">1</span></small>
                            <button class="btn btn-outline-secondary btn-sm" id="learnnewNextBtn">Next <i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success bg-opacity-10 border-0">
                        <h2 class="h5 mb-0"><i class="bi bi-check-circle text-success me-2"></i>Completed Topics</h2>
                    </div>
                    <div class="card-body">
                        <div id="completedContainer" class="list-group list-group-flush mb-3">
                            <div class="text-center py-4">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-outline-secondary btn-sm" id="completedPrevBtn"><i class="bi bi-chevron-left"></i> Prev</button>
                            <small class="text-muted">Page <span id="completedPageNum">1</span></small>
                            <button class="btn btn-outline-secondary btn-sm" id="completedNextBtn">Next <i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const learnnewContainer = document.getElementById("learnnewContainer");
        const completedContainer = document.getElementById("completedContainer");
        const searchInput = document.getElementById("searchInput");
        const searchBtn = document.getElementById("searchBtn");
        const learnnewPageNum = document.getElementById("learnnewPageNum");
        const learnnewPrevBtn = document.getElementById("learnnewPrevBtn");
        const learnnewNextBtn = document.getElementById("learnnewNextBtn");
        const completedPageNum = document.getElementById("completedPageNum");
        const completedPrevBtn = document.getElementById("completedPrevBtn");
        const completedNextBtn = document.getElementById("completedNextBtn");

        let learnnewSearch = '';
        let learnnewPage = 1;
        let completedPage = 1;

        async function updateLearnnew() {
            try {
                learnnewContainer.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                const response = await fetch(`../api/RequestAvailableTopics.php?search=${encodeURIComponent(learnnewSearch)}&page=${learnnewPage}`);
                const data = await response.json();
                learnnewContainer.innerHTML = '';
                if (data.success && data.topics && data.topics.length > 0) {
                    data.topics.forEach(topic => {
                        addLearnnewCard(topic.title);
                    });
                } 
                else {
                    learnnewContainer.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-2">No topics found</p>
                        </div>
                    `;
                }
                learnnewPageNum.textContent = learnnewPage;
                learnnewPrevBtn.disabled = learnnewPage <= 1;
                learnnewNextBtn.disabled = !data.topics || data.topics.length < 5;
            } 
            catch (error) {
                console.error('Error fetching topics:', error);
                learnnewContainer.innerHTML = `
                    <div class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                        <p class="mt-2">Failed to load topics</p>
                    </div>
                `;
            }
        }

        async function updateCompleted() {
            try {
                completedContainer.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                `;
                const response = await fetch(`../api/RequestSessionTopics.php?page=${completedPage}`);
                const data = await response.json();
                completedContainer.innerHTML = '';
                if (data.success && data.topics && data.topics.length > 0) {
                    data.topics.forEach(topic => {
                        addCompletedCard(topic.topic_title, topic.quiz_score);
                    });
                } 
                else {
                    completedContainer.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-2">No completed topics yet</p>
                        </div>
                    `;
                }
                completedPageNum.textContent = completedPage;
                completedPrevBtn.disabled = completedPage <= 1;
                completedNextBtn.disabled = !data.topics || data.topics.length < 5;
            } 
            catch (error) {
                console.error('Error fetching completed topics:', error);
                completedContainer.innerHTML = `
                    <div class="text-center py-4 text-danger">
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                        <p class="mt-2">Failed to load completed topics</p>
                    </div>
                `;
            }
        }

        function addLearnnewCard(topicTitle) {
            const card = document.createElement('div');
            card.className = 'list-group-item d-flex justify-content-between align-items-center px-0 gap-2';
            card.innerHTML = `
                <span class="text-truncate" style="min-width: 0;">${topicTitle}</span>
                <form method="post" class="flex-shrink-0">
                    <button type="submit" name="startSession" value="${topicTitle}" class="btn btn-primary btn-sm"><i class="bi bi-chat-dots me-1"></i>Chat</button>
                </form>
            `;
            learnnewContainer.appendChild(card);
        }

        function addCompletedCard(topicTitle, lastScore) {
            const card = document.createElement('div');
            card.className = 'list-group-item d-flex justify-content-between align-items-center px-0 bg-light gap-2';
            card.innerHTML = `
                <div class="overflow-hidden">
                    <div class="fw-medium text-truncate" style="min-width: 0;">${topicTitle}</div>
                    <span class="badge bg-success mt-1">Last Score: ${lastScore}%</span>
                </div>
                <form method="post" class="flex-shrink-0">
                    <button type="submit" name="startSession" value="${topicTitle}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Revisit</button>
                </form>
            `;
            completedContainer.appendChild(card);
        }

        searchBtn.addEventListener('click', () => {
            learnnewSearch = searchInput.value.trim();
            learnnewPage = 1;
            updateLearnnew();
        });

        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                learnnewSearch = searchInput.value.trim();
                learnnewPage = 1;
                updateLearnnew();
            }
        });

        learnnewPrevBtn.addEventListener('click', () => {
            if (learnnewPage > 1) {
                learnnewPage--;
                updateLearnnew();
            }
        });

        learnnewNextBtn.addEventListener('click', () => {
            learnnewPage++;
            updateLearnnew();
        });

        completedPrevBtn.addEventListener('click', () => {
            if (completedPage > 1) {
                completedPage--;
                updateCompleted();
            }
        });

        completedNextBtn.addEventListener('click', () => {
            completedPage++;
            updateCompleted();
        });

        updateLearnnew();
        updateCompleted();
    </script>
</body>
</html>