<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
$mail->SMTPDebug = 0; // Set to 2 for debugging during testing
$mail->isSMTP(); // Send using SMTP
$mail->Host       = 'mail.kipmetzsolutions.com'; // Your SMTP server
$mail->SMTPAuth   = true; // Enable SMTP authentication
$mail->Username   = 'rplusfieldteamdatabase@kipmetzsolutions.com'; // SMTP username
$mail->Password   = 'ynU(0HCq?TDt'; // SMTP password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS encryption
$mail->Port       = 587; // Port 587 for STARTTLS or 465 for SSL



    //Recipients
    $mail->setFrom('rplusfieldteamdatabase@kipmetzsolutions.com', 'R+ Field-Team Database');
    $mail->addAddress($emailaddress, $fullnames);     //Add a recipient
    $mail->addReplyTo('info@kipmetzsolutions.com', 'Information');

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $subject;
    $mail->Body    = $message;
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();   
} catch (Exception $e)
{  }