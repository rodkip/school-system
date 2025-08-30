<?php
if (isset($_SESSION['messagestate']) && isset($_SESSION['mess'])) {
    $messagestate = $_SESSION['messagestate'];
    $mess = $_SESSION['mess'];

    switch ($messagestate) {
        case 'added':
            $popupClass = 'popup-success';
            $icon = '<i class="fa-solid fa-graduation-cap"></i>';
            $title = 'Success!';
            break;
        case 'deleted':
            $popupClass = 'popup-error';
            $icon = '<i class="fa-solid fa-eraser"></i>';
            $title = 'Deleted!';
            break;
        case 'info':
            $popupClass = 'popup-info';
            $icon = '<i class="fa-solid fa-book-open"></i>';
            $title = 'Information';
            break;
        case 'warning':
            $popupClass = 'popup-warning';
            $icon = '<i class="fa-solid fa-bell"></i>';
            $title = 'Warning!';
            break;
        default:
            $popupClass = 'popup-default';
            $icon = '<i class="fa-solid fa-school"></i>';
            $title = 'Notice';
    }

    echo <<<HTML
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <div class="popup-overlay animate__animated animate__fadeIn" id="popupOverlay" onclick="dismissPopup()"></div>
    <div class="popup-container animate__animated animate__zoomIn" id="popupContainer">
        <div class="popup-message {$popupClass}" id="popupmessage">
            <div class="popup-header">
                <div class="popup-icon">{$icon}</div>
                <div class="popup-title">{$title}</div>
                <button class="popup-close" onclick="dismissPopup()">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="popup-body">
                <span class="popup-text">{$mess}</span>
            </div>
            <div class="popup-progress"></div>
        </div>
    </div>
    <style>
        :root {
            --success-color: #28a745;
            --error-color: #dc3545;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --default-color: #6c757d;
            --chalkboard-dark: #1f2d1f;
            --parchment: #fdf8e1;
            --ink: #2d2d2d;
        }

        .popup-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            pointer-events: none;
        }

        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9998;
        }

        .popup-message {
            width: 700px;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.25);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            pointer-events: auto;
            position: relative;
            border: 4px solid #fff;
            background: var(--chalkboard-dark);
            color: var(--parchment);
            transform-origin: center;
        }

        .popup-header {
            display: flex;
            align-items: center;
            padding: 18px 20px;
            background: var(--parchment);
            border-bottom: 2px dashed #d1c7a2;
        }

        .popup-icon {
            font-size: 24px;
            margin-right: 12px;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: white;
        }

        .popup-success .popup-icon { background-color: var(--success-color); }
        .popup-error .popup-icon { background-color: var(--error-color); }
        .popup-info .popup-icon { background-color: var(--info-color); }
        .popup-warning .popup-icon { background-color: var(--warning-color); }
        .popup-default .popup-icon { background-color: var(--default-color); }

        .popup-title {
            font-weight: 700;
            font-size: 20px;
            color: var(--ink);
            flex-grow: 1;
        }

        .popup-close {
            background: none;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ink);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 18px;
        }

        .popup-close:hover { background-color: rgba(0,0,0,0.1); }

        .popup-body {
            padding: 18px 22px;
            background: var(--chalkboard-dark);
        }

        .popup-text {
            color: var(--parchment);
            line-height: 1.5;
            font-size: 16px;
        }

        /* Chalk underline progress bar */
        .popup-progress {
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%);
            height: 3px;
            width: 80%;
            background: rgba(255,255,255,0.15);
            border-radius: 2px;
            overflow: hidden;
        }

        .popup-progress::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background: #fff;
            box-shadow: 0 0 6px #fff, 0 0 12px #fef3c7;
            animation: chalkErase 5s linear forwards;
            transform-origin: left;
        }

        @keyframes chalkErase {
            0%   { transform: scaleX(1); opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: scaleX(0); opacity: 0; }
        }

        @keyframes schoolPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        .popup-message:hover { animation: schoolPulse 1s ease infinite; }
    </style>
    <script>
        function dismissPopup() {
            const container = document.getElementById('popupContainer');
            const overlay = document.getElementById('popupOverlay');
            if (container && overlay) {
                container.classList.remove('animate__zoomIn');
                container.classList.add('animate__zoomOut');
                overlay.classList.remove('animate__fadeIn');
                overlay.classList.add('animate__fadeOut');
                setTimeout(() => {
                    if (container && overlay) {
                        container.remove();
                        overlay.remove();
                    }
                }, 500);
            }
        }

        setTimeout(dismissPopup, 5000);

        const popup = document.getElementById('popupmessage');
        if (popup) {
            const progressBar = popup.querySelector('.popup-progress');
            popup.addEventListener('mouseenter', () => {
                if (progressBar) progressBar.style.animationPlayState = 'paused';
            });
            popup.addEventListener('mouseleave', () => {
                if (progressBar) progressBar.style.animationPlayState = 'running';
            });
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') dismissPopup();
        });
    </script>
HTML;
 
    unset($_SESSION['messagestate']);
    unset($_SESSION['mess']);
}
?>
