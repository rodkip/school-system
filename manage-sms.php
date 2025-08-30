<?php
session_start();
error_reporting(E_ALL); // Use full error reporting for debugging
include('includes/dbconnection.php');

// === TALKSASA API CONFIGURATION ===
$apiEndpoint = 'https://bulksms.talksasa.com/api/v3/sms/send';
$apiToken = '772|eLMZ3e9YhWWXeeExMmYiKtWBvyKMGsKDxqV6cFDj153678e3';

$statusMessage = '';
$statusClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($phone) || empty($message)) {
        $statusMessage = 'Phone number and message are required.';
        $statusClass = 'danger';
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
        $statusMessage = 'Invalid phone number format.';
        $statusClass = 'danger';
    } else {
        // === BUILD PAYLOAD ===
        $postData = json_encode([
            'recipient' => $phone,
            'sender_id' => 'BookPrestig', // Your approved sender ID
            'message' => $message,
            'callback_url' => ''
        ]);

        $headers = [
            "Accept: application/json",
            "Content-Type: application/json",
            "Authorization: Bearer {$apiToken}"
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true // Enable in production
        ]);

        $apiResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            $statusMessage = 'cURL Error: ' . $error;
            $statusClass = 'danger';
            file_put_contents('talksasa_error.log', date('Y-m-d H:i:s') . " - cURL Error: $error\n", FILE_APPEND);
        } else {
            $responseData = json_decode($apiResponse, true);
            file_put_contents('talksasa_response.log', date('Y-m-d H:i:s') . " - HTTP $httpCode\nRequest: $postData\nResponse: $apiResponse\n\n", FILE_APPEND);

            if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
                $statusMessage = 'SMS sent successfully! Message ID: ' . $responseData['message_id'];
                $statusClass = 'success';
            } else {
                $errorMsg = $responseData['message'] ?? 'Unknown error occurred';
                $statusMessage = 'Failed to send SMS. Error: ' . htmlspecialchars($errorMsg);
                $statusClass = 'danger';
            }
        }

        curl_close($ch);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send SMS | Messaging System</title>
    
    <!-- CSS imports -->
    <link href="assets/plugins/bootstrap/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/plugins/pace/pace-theme-big-counter.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/main-style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .sms-form-container {
            background: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 2rem;
        }
        
        .sms-form-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .form-control-lg {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }
        
        .form-control-lg:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        
        .btn-send {
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(58, 123, 213, 0.3);
        }
        
        .invalid-feedback {
            font-size: 0.85rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <?php include_once('includes/sidebar.php'); ?>
        
        <!-- Page content -->
        <div id="page-content-wrapper">
            <!-- Top navbar -->
            <?php include_once('includes/header.php'); ?>
            
            <div id="page-wrapper">
                <div class="row">
                  <br>
                  <br>
                    <div class="col-lg-12">
                        <div class="container py-4">
                            <div class="sms-form-container">
                                <div class="sms-form-header">
                                    <h2 class="mb-0 text-primary">
                                        <i class="fas fa-sms me-2"></i>Send SMS via TalkSasa
                                    </h2>
                                    <p class="text-muted mt-2">Send messages using TalkSasa Bulk SMS API</p>
                                </div>
                                
                                <?php if ($statusMessage): ?>
                                    <div class="alert alert-<?= htmlspecialchars($statusClass) ?> alert-dismissible fade show">
                                        <?= htmlspecialchars($statusMessage) ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST" novalidate class="needs-validation">
                                    <div class="mb-4">
                                        <label for="phone" class="form-label fw-semibold">
                                            <i class="fas fa-mobile-alt me-2"></i>Recipient Phone Number
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="tel" class="form-control form-control-lg" 
                                                   name="phone" id="phone" 
                                                   placeholder="+254712345678" required
                                                   pattern="\+?[0-9]{10,15}">
                                        </div>
                                        <div class="invalid-feedback">
                                            Please provide a valid phone number (10-15 digits, + optional)
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="message" class="form-label fw-semibold">
                                            <i class="fas fa-comment-alt me-2"></i>Message
                                        </label>
                                        <textarea class="form-control form-control-lg" name="message" id="message" 
                                                  rows="5" placeholder="Type your message here..." 
                                                  required></textarea>
                                        <div class="invalid-feedback">
                                            Please enter your message
                                        </div>
                                        <div class="mt-2 text-end text-muted small">
                                            <span id="char-count">0</span>/160 characters
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                        <button type="submit" class="btn btn-send text-white px-4 py-2">
                                            <i class="fas fa-paper-plane me-2"></i>Send via TalkSasa
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript imports -->
    <script src="assets/plugins/jquery-1.10.2.js"></script>
    <script src="assets/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="assets/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="assets/plugins/pace/pace.js"></script>
    <script src="assets/scripts/siminta.js"></script>
    
    <script>
        // Form validation
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
        
        // Character counter for message
        document.getElementById('message').addEventListener('input', function() {
            document.getElementById('char-count').textContent = this.value.length;
        });
        
        // Clear alerts after 5 seconds
        window.setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
    </script>
</body>
</html>