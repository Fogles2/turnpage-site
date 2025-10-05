<?php
require_once 'config.php';

header('Content-Type: application/json');

function respond($ok,$msg){
    echo json_encode(['success'=>$ok,'message'=>$msg]);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email.');
}

// Store subscriber locally
$line = date('c') . "," . $email . "," . ($_SERVER['REMOTE_ADDR'] ?? '-') . "\n";
@file_put_contents(__DIR__ . '/subscribers.csv', $line, FILE_APPEND | LOCK_EX);

$subject = 'New maintenance subscriber: ' . $email;
$body = "A new user subscribed to maintenance updates.\n\nEmail: $email\nTime: " . date('r');

$sent = false;
$err = '';

// Try PHPMailer (Composer) if configured
$mailerAvailable = file_exists(__DIR__ . '/vendor/autoload.php') && !empty(SMTP_HOST);
if ($mailerAvailable) {
    try {
        require __DIR__ . '/vendor/autoload.php';
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->Port = SMTP_PORT;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->setFrom(NOTIFY_FROM, 'Turnpage');
        $mail->addAddress(NOTIFY_TO);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
        $sent = true;
    } catch (Exception $e) {
        $err = $e->getMessage();
    }
}

// Fallback to PHP mail()
if (!$sent) {
    $headers = 'From: ' . NOTIFY_FROM . "\r\n" . 'Reply-To: ' . NOTIFY_FROM;
    $sent = @mail(NOTIFY_TO, $subject, $body, $headers);
}

if ($sent) respond(true, 'Thanks! Check inbox for updates soon.');
respond(false, 'Could not send notification. Please try again later.' . ($err? ' (' . $err . ')' : ''));
?>