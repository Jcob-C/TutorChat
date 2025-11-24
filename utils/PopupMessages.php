<?php
function setNewPopupMessage($message) {
    $_SESSION['popupMessage'] = $message;
}

function displayPopupMessage() {
    if (isset($_SESSION['popupMessage'])) {
        echo '
        <style>
        #popupMessage {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            
            background: rgba(184, 184, 184, 1);
            padding: 20px 30px;
            border-radius: 10px;
            
            z-index: 9999;
            text-align: center;
            
            position: relative;
            display: block;
            
            width: 300px;
            max-width: 90%;
            box-sizing: border-box;
        }
        #closePopup {
            position: absolute;
            top: 5px;
            right: 5px;
            color: black;
            
            background: transparent;
            border: none;
            font-size: 20px;
            cursor: pointer;
        }
        #popupMessage span {
            color: black;
            display: block;
            margin-top: 20px;
            word-wrap: break-word;
        }
        </style>
        <div id="popupMessage"> 
            <button id="closePopup">X</button> 
            <span>' . $_SESSION['popupMessage'] . '</span> 
        </div>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            var closeBtn = document.getElementById("closePopup");
            if (closeBtn) {
                closeBtn.addEventListener("click", function() {
                    var popup = document.getElementById("popupMessage");
                    if (popup) {
                        popup.style.display = "none";
                    }
                });
            }
        });
        </script>
        ';
        unset($_SESSION['popupMessage']);
    }
}
?>
