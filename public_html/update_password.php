<?php
session_start();
require 'vendor/autoload.php';

$conn = new mysqli("...", "...", "...", "...", ...);
if ($conn->connect_error) die("DB Error");

$email = $_POST['email'] ?? '';
$token = $_POST['token'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($new_password !== $confirm || strlen($new_password) < 8) {
    die("Password mismatch or too short.");
}

// Get latest unused token
$stmt = $conn->prepare("SELECT id, token_hash, expires_at, used FROM password_resets WHERE email = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) die("Invalid or expired token.");
$row = $result->fetch_assoc();

if ($row['used'] || strtotime($row['expires_at']) < time()) {
    die("This reset link is no longer valid.");
}

// Validate token
if (!password_verify($token, $row['token_hash'])) die("Invalid token.");

// Update password
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);
$update = $conn->prepare("UPDATE users SET password = ?, current_session_id = NULL WHERE email = ?");
$update->bind_param("ss", $new_hash, $email);
$update->execute();

// Mark token as used
$markUsed = $conn->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
$markUsed->bind_param("i", $row['id']);
$markUsed->execute();

// Send notification
$mail = new PHPMailer\PHPMailer\PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'email';
    $mail->Password = '...';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('email', 'Lan Bakery');
    $mail->addAddress($email);
    $mail->Subject = 'Your Password Has Been Changed';
    $mail->Body = "Hi, your password has just been changed. If this wasn't you, please contact support immediately.";

    $mail->send();
} catch (Exception $e) {}

echo "Password updated. You may now <a href='login.html'>login</a>.";
