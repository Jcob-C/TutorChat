<?php
require_once __DIR__ . '/../utils/PageBlocker.php';
require_once __DIR__ . '/../config/db.php';

session_start();
$conn = new mysqli(host, user, pass, db);
redirectUnauthorized($conn);
redirectAdmin();
redirectFromTutor();
?>

<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TutorChat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/Style.css">
    <link rel="stylesheet" href="../assets/PopupMessage.css">
</head>
<body class="h-100 d-flex flex-column overflow-hidden">
    <div class="bg-white border-bottom flex-shrink-0">
        <div class="container-fluid py-3">
            <div class="row align-items-center g-3">
                <div class="col-lg-7 col-md-12">
                    <h4 class="mb-2" id="lessonTitle"><?= $_SESSION['ongoingTutorSession']['topic'] ?></h4>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-5 fw-semibold" id="currentSection">Introduction</span>
                    </div>
                </div>
                <div class="col-lg-5 col-md-12">
                    <div class="d-flex gap-2 justify-content-lg-end">
                        <button class="btn btn-outline-primary" onclick="window.location.href='learn.php'">
                            <i class="bi bi-x-circle me-2"></i> Exit    
                        </button>
                        <button class="btn btn-outline-primary" onclick="popupLessonPlan()">
                            <i class="bi bi-file-text me-2"></i> Lesson Plan
                        </button>
                        <button class="btn btn-success" id="saveSessionButton" onclick="handleSaveClick()">
                            <i class="bi bi-check-circle me-2"></i> Save Session
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="flex-grow-1 bg-light overflow-auto" id="chatMessagesContainer">
        <div class="container-fluid p-4">



        </div>
    </div>
    <div class="bg-white border-top flex-shrink-0">
        <div class="container-fluid p-3">
            <div class="row align-items-center g-2">
                <div class="col-lg-9 col-md-8 col-12">
                    <div class="input-group input-group-lg">
                        <input type="text" id="messageInput" class="form-control fs-5" placeholder="Type your message..." aria-label="Message">
                        <button class="btn btn-primary px-4 fs-5" type="button" id="sendButton" onclick="handleSendClick()">
                            <i class="bi bi-send-fill me-2"></i> Send
                        </button>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-12">
                    <button class="btn btn-outline-secondary btn-lg w-100 fs-5" id="nextSectionButton" onclick="nextSection()">
                        <i class="bi bi-arrow-right-circle me-2"></i> Next Section
                    </button>
                    <button class="btn btn-outline-secondary btn-lg w-100 fs-5" id="previousSectionButton" onclick="previousSection()" disabled>
                        <i class="bi bi-arrow-left-circle me-2"></i> Previous Section
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="lessonPlanModal" tabindex="-1" aria-labelledby="lessonPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="lessonPlanModalLabel">Lesson Plan</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body fs-5" id="lessonPlanContent">
                    <?= $_SESSION["ongoingTutorSession"]["plan"] ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../utils/PopupMessage/.js"></script>
    <script>
        let transcript = [];
        let section = 'Introduction';
        const sections = ['Introduction', 'Section 1', 'Section 2', 'Section 3', 'Conclusion'];
        let currentSectionIndex = 0;
        let isWaitingForResponse = false;
        let lastAIMessage = "";
        let lastUserMessage = "";
        let allowLeave = false;

        const messagesContainer = document.getElementById('chatMessagesContainer').querySelector('.container-fluid');

        function handleSendClick() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            if (message && !isWaitingForResponse) {
                sendMessage(message);
                input.value = '';
            }
        }

        function sendMessage(newMessage) {
            if (isWaitingForResponse) return;
            isWaitingForResponse = true;
            disableInput(true);
            addMessage(true, newMessage);
            getAIResponse().then(aiResponse => {
                addMessage(false, aiResponse);
                isWaitingForResponse = false;
                disableInput(false);
            }).catch(error => {
                console.error('Error getting AI response:', error);
                displayPopupMessage('Error getting AI response. Please try again.');
                isWaitingForResponse = false;
                disableInput(false);
            });
        }
        
        function addMessage(isUser, message) {
            transcript.push({ isUser, message, timestamp: new Date().toISOString() });           
            const messageDiv = document.createElement('div');
            messageDiv.className = `d-flex mb-3 ${isUser ? 'justify-content-end' : 'justify-content-start'}`;          
            const bubble = document.createElement('div');
            bubble.className = `p-3 rounded-3 fs-5 ${isUser ? 'bg-primary text-white' : 'bg-white border'}`;
            bubble.style.maxWidth = '75%';
            bubble.style.wordWrap = 'break-word';
            bubble.style.overflowWrap = 'break-word';
            bubble.innerHTML = message;         
            messageDiv.appendChild(bubble);
            messagesContainer.appendChild(messageDiv);       
            const container = document.getElementById('chatMessagesContainer');
            const messageTop = messageDiv.offsetTop;
            container.scrollTop = messageTop - 110;
            if (isUser) {
                lastUserMessage = message;
            } else {
                lastAIMessage = message;
            }
        }

        async function getAIResponse() {
            try {
                const lastMessage = transcript[transcript.length - 1];           
                const response = await fetch('../api/RequestChatResponse.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        aimessage: lastAIMessage,
                        usermessage: lastUserMessage,
                        section: section
                    })
                });        
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }           
                const aiMessage = await response.text();
                return aiMessage;
            } catch (error) {
                throw error;
            }
        }

        function nextSection() {
            if (currentSectionIndex < sections.length - 1) {
                currentSectionIndex++;
                section = sections[currentSectionIndex];
                document.getElementById('currentSection').textContent = section;
                
                // Enable previous button since we're no longer at the start
                document.getElementById('previousSectionButton').disabled = false;
                
                // Disable next button if at last section
                if (currentSectionIndex === sections.length - 1) {
                    document.getElementById('nextSectionButton').disabled = true;
                }

                sendMessage(`Move forward to ${section}.`);
            }
        }

        function previousSection() {
            if (currentSectionIndex > 0) {
                currentSectionIndex--;
                section = sections[currentSectionIndex];
                document.getElementById('currentSection').textContent = section;
                
                // Enable next button if it was disabled
                document.getElementById('nextSectionButton').disabled = false;
                
                // Disable previous button if at first section
                if (currentSectionIndex === 0) {
                    document.getElementById('previousSectionButton').disabled = true;
                }

                sendMessage(`Move back to ${section}.`);
            }
        }

        function popupLessonPlan() {
            const modalElement = document.getElementById('lessonPlanModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }

        async function handleSaveClick() {
            if (isWaitingForResponse) {
                displayPopupMessage('Please wait for the current response to complete.');
                return;
            }        
            await saveSession();
        }

        async function saveSession() {
            const saveButton = document.getElementById('saveSessionButton');
            saveButton.disabled = true;
            saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';          
            try {
                const response = await fetch('../api/SaveSession.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ transcript })
                });             
                const result = await response.text();             
                if (result.trim() === 'saved') {
                    displayPopupMessage('Session saved successfully! Redirecting to quiz...');
                    setTimeout(() => {
                        allowLeave = true;
                        window.location.href = 'quiz.php';
                    }, 1500);
                } else {
                    displayPopupMessage('Failed to save session. Please try again.');
                    saveButton.disabled = false;
                    saveButton.innerHTML = '<i class="bi bi-check-circle me-2"></i> Save Session';
                }
            } catch (error) {
                console.error('Error saving session:', error);
                displayPopupMessage('Error saving session. Please try again.');
                saveButton.disabled = false;
                saveButton.innerHTML = '<i class="bi bi-check-circle me-2"></i> Save Session';
            }
        }

        function disableInput(disabled) {
            document.getElementById('messageInput').disabled = disabled;
            document.getElementById('sendButton').disabled = disabled;
        }

        document.getElementById("messageInput").addEventListener("keydown", function (event) {
            if (event.key === "Enter") {
                event.preventDefault(); // prevent form submit if inside a form
                handleSendClick();      // same action as clicking the button
            }
        });

        window.addEventListener("beforeunload", function (event) {
            if (!allowLeave) {
                event.preventDefault();
                event.returnValue = ""; // Required for Chrome, Edge, Firefox
            }
        });

        sendMessage("Let's chat!");
    </script>
</body>
</html>