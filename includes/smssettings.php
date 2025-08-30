<?php
// Talksasa SMS sending script - adapted from PHPMailer structure

// Talksasa API configuration
$apiEndpoint = 'https://bulksms.talksasa.com/api/v3/sms/send';
$apiToken = '989|CIwbNQR0XHeVSttXARSdBtX6lf1xnouPzieBoFOD9e6d2754';

// Ensure required values are set
if (!empty($mobilenumber) && !empty($message)) {
    if (!preg_match('/^\+?[0-9]{10,15}$/', $mobilenumber)) {
        // Invalid phone number format
        error_log("Invalid phone number format: $mobilenumber", 3, 'talksasa_error.log');
    } else {
        // Prepare payload
        $postData = json_encode([
            'recipient' => $mobilenumber,
            'sender_id' => 'TALKSASA', // Replace with your approved sender ID
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
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $apiResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            error_log(date('Y-m-d H:i:s') . " - cURL Error: $error\n", 3, 'talksasa_error.log');
        } else {
            $responseData = json_decode($apiResponse, true);
            file_put_contents('talksasa_response.log', date('Y-m-d H:i:s') . " - HTTP $httpCode\nRequest: $postData\nResponse: $apiResponse\n\n", FILE_APPEND);

            if ($httpCode === 200 && isset($responseData['status']) && $responseData['status'] === 'success') {
                // Success
                // You can use: $responseData['message_id'];
            } else {
                $errorMsg = $responseData['message'] ?? 'Unknown error';
                error_log(date('Y-m-d H:i:s') . " - API Error: $errorMsg\n", 3, 'talksasa_error.log');
            }
        }

        curl_close($ch);
    }
} else {
    error_log("Phone number or message not provided.\n", 3, 'talksasa_error.log');
}
?>
