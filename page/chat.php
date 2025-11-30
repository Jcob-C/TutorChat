<?php
require_once __DIR__ . '/../utils/CleanerFunctions.php';
require_once __DIR__ . '/../utils/PageBlocker.php';
require_once __DIR__ . '/../utils/database/Topics.php';
require_once __DIR__ . '/../utils/popupmessages/back.php';

session_start();
loginBlock();
redirectIfSkippedSessionProcedure('chat');
redirectAdmin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TutorChat Session</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/theme.css">
    <link rel="stylesheet" href="../assets/popupMessage.css">
</head>
<body>
    <div class="container-fluid d-flex flex-column p-3" style="height: 100vh; max-height: 100vh; overflow: hidden;">
        <!-- Header Section -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-center flex-grow-1">
                        <h1 class="h4 fw-bold text-white mb-0">
                            <i class="bi bi-chat-dots-fill text-brand"></i> TutorChat
                        </h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Topic and End Session -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="bg-dark bg-opacity-75 border border-secondary rounded-3 p-3 shadow">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 id="topicTitle" class="h4 fw-bold mb-0 text-brand"><?= getTopicTitle($_SESSION['tutorSession']['topicID']) ?></h2>
                        <a id="saveSessionBtn" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-box-arrow-right"></i> Save Session
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Container -->
        <div class="row flex-grow-1 mb-3" style="min-height: 0;">
            <div class="col-12 h-100">
                <div id="chatContainer" class="bg-dark bg-opacity-75 border border-secondary rounded-3 p-4" style="height: 100%; overflow-y: auto; overflow-x: hidden;">
                </div>
            </div>
        </div>

        <!-- Input Section -->
        <div class="row">
            <div class="col-12">
                <div class="bg-dark bg-opacity-50 border border-secondary rounded-3 p-3">
                    <div class="d-flex gap-2">
                        <textarea id="chatInput" class="form-control bg-dark text-white border-secondary" rows="2" placeholder="Type your message..."></textarea>
                        <button class="btn btn-brand px-4">
                            <i class="bi bi-send-fill"></i> Send
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../utils/popupmessages/front.js"></script>
    <script>
        const chatContainer = document.getElementById('chatContainer');
        const chatInput = document.getElementById('chatInput');
        const sendButton = document.querySelector('button');
        const topicTitle = document.getElementById("topicTitle").innerText.trim();

        const chatHistory = [];

        function addChatMessage(message, isUser = true) {
            // Save to history
            chatHistory.push({
                role: isUser ? "user" : "assistant",
                message: message,
                timestamp: new Date().toISOString()
            });

            const messageDiv = document.createElement('div');
            messageDiv.className = `chatMessage ${isUser ? 'userMessage' : 'aiMessage'} mb-3`;

            if (isUser) {
                messageDiv.innerHTML = `
                    <div class="d-flex align-items-start justify-content-end">
                        <div class="rounded-3 p-3 text-white" style="background: var(--primary-purple);">
                            <p class="mb-0">${message}</p>
                        </div>
                        <div class="bg-secondary bg-opacity-50 rounded-circle p-2 ms-2">
                            <i class="bi bi-person-fill text-white"></i>
                        </div>
                    </div>
                `;
            } else {
                messageDiv.innerHTML = `
                    <div class="d-flex align-items-start">
                        <div class="bg-secondary rounded-circle p-2 me-2">
                            <i class="bi bi-robot text-white fs-5"></i>
                        </div>
                        <div class="bg-secondary bg-opacity-75 rounded-3 p-3">
                            <p class="mb-0 text-white">${markdownToHTML(message)}</p>
                        </div>
                    </div>
                `;
            }

            chatContainer.appendChild(messageDiv);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        let canSend = true; // flag to control sending in sendMessage()

        async function sendMessage() {
            const message = chatInput.value.trim();

            if (!canSend) {
                displayPopupMessage("Please wait for the AI to respond before sending another message.");
                return;
            }

            if (!message) return;

            // Add user message to UI + chat history
            addChatMessage(message, true);
            chatInput.value = '';
            canSend = false;

            // Prepare payload for backend
            const payload = {
                chatHistory: chatHistory,
                newMessage: message,
                topicTitle: topicTitle
            };

            try {
                const response = await fetch('../api/Responder.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error: ${response.status}`);
                }

                const result = await response.text(); 
                // PHP echoes back plain text → no JSON decoding

                // Add AI response to chat
                addChatMessage(result, false);
            } 
            catch (error) {
                console.error("Error contacting backend:", error);
                addChatMessage("Failed to get response. Please try again.", false);
            }

            canSend = true; // unlock sending
        }

        function markdownToHTML(md) {
            return md
                .replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/`([^`]+)`/g, '<code>$1</code>')
                .replace(/\n/g, '<br>');
        }

        sendButton.addEventListener('click', sendMessage);
        
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        let allowLeave = false;

        window.addEventListener("beforeunload", function (e) {
            if (!allowLeave) {
                e.preventDefault();
                e.returnValue = "";
            }
        });

        // Save Session Button Listener
        document.getElementById("saveSessionBtn").addEventListener("click", () => {
            const jsonData = JSON.stringify(chatHistory);

            fetch("../api/saveMessages.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: jsonData
            })
            .then(res => res.text())
            .then(response => {
                if (response.trim() === "saved") {
                    allowLeave = true;
                    window.location.href = "posttest.php";
                } else {
                    displayPopupMessage("Please try again.");
                }
            })
            .catch(err => {
                displayPopupMessage("Please try again.");
            });
        });
    </script>
</body>
</html>