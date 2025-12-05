<?php
require_once __DIR__ . '/../utils/PageBlocker.php';
require_once __DIR__ . '/../config/db.php';

$conn = new mysqli(host,user,pass,db);
session_start();
redirectUnauthorized($conn);
redirectAdmin();

if (!isset($_SESSION['ongoingTutorSession']) || !isset($_SESSION['ongoingTutorSession']['topicTitle']) || !isset($_SESSION['ongoingTutorSession']['topicPlan'])) {
    header('Location: learn.php'); exit;
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
</head>
<body class="bg-light">
    <!-- Header -->
    <nav class="navbar navbar-light bg-white shadow-sm sticky-top">
        <div class="container-fluid px-4">
            <h1 class="h3 mb-0 text-nowrap">
                <i class="bi bi-chat-dots-fill text-primary"></i> TutorChat
            </h1>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#topicPlanModal">
                    <i class="bi bi-journal-text"></i> View
                </button>
                <button class="btn btn-primary btn-sm" onclick="saveSession();">
                    <i class="bi bi-save"></i> Save
                </button>
            </div>
        </div>
    </nav>

    <!-- Topic Plan Modal -->
    <div class="modal fade" id="topicPlanModal" tabindex="-1" aria-labelledby="topicPlanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="topicPlanModalLabel">
                        <i class="bi bi-journal-text text-primary"></i> Tutor Plan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-muted">
                        <?php
                        if (isset($_SESSION['ongoingTutorSession']['topicPlan'])) {
                            echo $_SESSION['ongoingTutorSession']['topicPlan'];
                        } else {
                            echo '<p class="text-center">No topic plan available.</p>';
                        }
                        ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container-fluid d-flex flex-column" style="height: calc(100vh - 56px);">
        <div class="row justify-content-center flex-grow-1 overflow-hidden">
            <div class="col-12 col-lg-8 col-xl-6 d-flex flex-column h-100">
                
                <!-- Conversation Container -->
                <div class="flex-grow-1 overflow-auto p-3" id="conversationContainer">

                    <!-- Continue Button -->
                    <div class="row mb-3" hidden>
                        <div class="col-12 text-center">
                            <button class="btn btn-outline-primary" id="proceedButton">
                                <i class="bi bi-arrow-right-circle"></i> Next Section
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Input Area -->
                <div class="bg-white border-top p-3">
                    <div class="row g-2">
                        <div class="col">
                            <div class="input-group">
                                <input type="text" class="form-control border-secondary" placeholder="Type your message..." id="messageInput" aria-label="Message input">
                                <button class="btn btn-primary" type="button" id="sendButton">
                                    <i class="bi bi-send-fill"></i> Send
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../utils/PopupMessage/.js"></script>
    <script>
        const chatHistory = [];
        const conversationContainer = document.getElementById('conversationContainer');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const proceedButton = document.getElementById('proceedButton');
        
        let currentRequestId = 0;
        let currentSaveRequestId = 0;
        let allowLeave = false;

        function sendMessage(message) {
            if (!message || message.trim() === '') return;
            
            // Add message to chat history
            chatHistory.push({ role: 'user', content: message });
            
            // Add to UI
            addUIMessage(true, message);
            
            // Clear input
            messageInput.value = '';
            
            // Disable buttons
            sendButton.disabled = true;
            proceedButton.disabled = true;
            
            // Fetch response
            fetchResponse();
        }

        function addUIMessage(isUser, message) {
            const row = document.createElement('div');
            row.className = 'row mb-3';
            
            const col = document.createElement('div');
            col.className = 'col-12';
            
            const currentTime = new Date().toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
            
            if (isUser) {
                col.innerHTML = `
                    <div class="d-flex align-items-start justify-content-end">
                        <div class="bg-primary text-white rounded-3 shadow-sm p-3 flex-grow-1" style="max-width: 75%;">
                            <p class="mb-0">${escapeHtml(message)}</p>
                            <small class="text-white-50">${currentTime}</small>
                        </div>
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center ms-2 flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    </div>
                `;
            } else {
                col.innerHTML = `
                    <div class="d-flex align-items-start">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bi bi-robot"></i>
                        </div>
                        <div class="bg-white rounded-3 shadow-sm p-3 flex-grow-1">
                            <p class="mb-0 text-dark">${message}</p>
                            <small class="text-muted">${currentTime}</small>
                        </div>
                    </div>
                `;
            }

            row.appendChild(col);
            
            // Remove proceed button if it exists
            const existingProceedBtn = document.querySelector('#proceedButton')?.closest('.row');
            if (existingProceedBtn) {
                existingProceedBtn.remove();
            }
            
            conversationContainer.appendChild(row);
            
            // Add proceed button back after AI messages
            if (!isUser) {
                const proceedRow = document.createElement('div');
                proceedRow.className = 'row mb-3';
                proceedRow.innerHTML = `
                    <div class="col-12 text-center">
                        <button class="btn btn-outline-primary" id="proceedButton">
                            <i class="bi bi-arrow-right-circle"></i> Next Section
                        </button>
                    </div>
                `;
                conversationContainer.appendChild(proceedRow);
                
                // Re-attach event listener to new button
                document.getElementById('proceedButton').addEventListener('click', () => {
                    sendMessage("Let's proceed with the next section of the tutor plan.");
                });
            }
            
            // Scroll to bottom
            row.scrollIntoView({ behavior: "smooth", block: "start" });
        }

        async function fetchResponse() {
            const requestId = ++currentRequestId;
            const timeoutDuration = 30000; // 30 seconds
            let timeoutId;
            let responseReceived = false;
            
            // Add loading indicator
            const loadingRow = document.createElement('div');
            loadingRow.className = 'row mb-3';
            loadingRow.id = 'loadingIndicator';
            loadingRow.innerHTML = `
                <div class="col-12">
                    <div class="d-flex align-items-start">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bi bi-robot"></i>
                        </div>
                        <div class="bg-white rounded-3 shadow-sm p-3 flex-grow-1">
                            <p class="mb-0 text-muted">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Thinking...
                            </p>
                        </div>
                    </div>
                </div>
            `;
            conversationContainer.appendChild(loadingRow);
            conversationContainer.scrollTop = conversationContainer.scrollHeight;
            
            const timeoutPromise = new Promise((_, reject) => {
                timeoutId = setTimeout(() => {
                    reject(new Error('Request timeout'));
                }, timeoutDuration);
            });
            
            const fetchPromise = fetch('../api/RequestChatResponse.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ history: chatHistory })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            });
            
            try {
                const responseText = await Promise.race([fetchPromise, timeoutPromise]);
                clearTimeout(timeoutId);
                
                // Remove loading indicator
                const loadingEl = document.getElementById('loadingIndicator');
                if (loadingEl) loadingEl.remove();
                
                // Check if this is still the current request
                if (requestId !== currentRequestId) {
                    console.log('Ignoring outdated response');
                    return;
                }
                
                responseReceived = true;
                
                // Add AI response to chat history
                chatHistory.push({ role: 'assistant', content: responseText });
                
                // Add to UI
                addUIMessage(false, responseText);
                
            } catch (error) {
                clearTimeout(timeoutId);
                
                // Remove loading indicator
                const loadingEl = document.getElementById('loadingIndicator');
                if (loadingEl) loadingEl.remove();
                
                // Check if this is still the current request
                if (requestId !== currentRequestId) {
                    return;
                }
                
                console.error('Error fetching response:', error);
                addUIMessage(false, 'Failed to get response. Please try again.');
                
            } finally {
                // Re-enable buttons only if this is still the current request
                if (requestId === currentRequestId) {
                    sendButton.disabled = false;
                    proceedButton.disabled = false;
                }
            }
        }

        async function saveSession() {
            try {
                displayPopupMessage("Saving session...");
                const response = await fetch('../api/SaveSession.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(chatHistory)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.text();

                if (result == "Saved.") {
                    allowLeave = true;
                    window.location.href = "learn.php"
                } else {
                    displayPopupMessage("Failed, please try again.");
                }
            } catch (error) {
                displayPopupMessage("Failed, please try again.");
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        sendButton.addEventListener('click', () => {
            const message = messageInput.value.trim();
            if (message) {
                sendMessage(message);
            }
        });

        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                const message = messageInput.value.trim();
                if (message) {
                    sendMessage(message);
                }
            }
        });

        window.addEventListener("beforeunload", function (event) {
            if (!allowLeave) {
                event.preventDefault();         
                event.returnValue = "";  
            }   
        });

        sendMessage("Let's Chat!");
    </script>
</body>
</html>