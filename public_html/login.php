<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection
$servername = "127.0.0.1";
$db_username = "u875650075_idlan";
$db_password = "Idlan@123";
$dbname = "u875650075_idlan_database";
$port = 3306;

$conn = new mysqli($servername, $db_username, $db_password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed. Please try again later.");
}

require_once 'logger.php';

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['pass'])) {

    // ✅ Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "CSRF token mismatch. Please refresh the page and try again.";
        exit();
    }

    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['pass']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
        echo "Invalid login credentials.";
        exit();
    }

    // Check reCAPTCHA response
    $recaptcha_secret = "6Lf-LEgrAAAAAHD0nLruj03Tu51TgoDDgVLw8Z-T";
$recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

// Validate reCAPTCHA token format: only letters, numbers, dash, underscore allowed
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $recaptcha_response)) {
    echo "Invalid reCAPTCHA response.";
    exit();
}

$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response");
$response_keys = json_decode($response, true);


    if (intval($response_keys["success"]) !== 1) {
        echo "Please complete the CAPTCHA.";
        exit();
    }

    $ip_address = $_SERVER['REMOTE_ADDR'];
    $attempt_window = 15; // minutes
    $max_attempts = 5;

    // Count failed attempts in the last 15 minutes
    $checkAttempts = $conn->prepare("
        SELECT COUNT(*) FROM login_attempts 
        WHERE ip_address = ? AND attempt_time > (NOW() - INTERVAL ? MINUTE)
    ");
    $checkAttempts->bind_param("si", $ip_address, $attempt_window);
    $checkAttempts->execute();
    $checkAttempts->bind_result($attempt_count);
    $checkAttempts->fetch();
    $checkAttempts->close();

    if ($attempt_count >= $max_attempts) {
        echo "Too many failed attempts. Please try again later.";
        exit();
    }

    // Check user in DB
    $stmt = $conn->prepare("SELECT user_id, username, password, is_admin, current_session_id FROM users WHERE email = ?");
    if (!$stmt) {
        echo "Server error. Please try again later.";
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $logAttempt = $conn->prepare("INSERT INTO login_attempts (ip_address, email, attempt_time) VALUES (?, ?, NOW())");
        $logAttempt->bind_param("ss", $ip_address, $email);
        $logAttempt->execute();
        $logAttempt->close();
        
        log_user_action($conn, null, "Failed login: email not found ($email)", $_SERVER['PHP_SELF']);


        echo "Invalid login credentials.";
        exit();
    }

    $row = $result->fetch_assoc();
    $user_id = $row['user_id'];
    $username = $row['username'];
    $hashed_password = $row['password'];
    $is_admin = $row['is_admin'];
    $existing_session_id = $row['current_session_id'];

    if (!password_verify($password, $hashed_password)) {
        $logAttempt = $conn->prepare("INSERT INTO login_attempts (ip_address, email, attempt_time) VALUES (?, ?, NOW())");
        $logAttempt->bind_param("ss", $ip_address, $email);
        $logAttempt->execute();
        $logAttempt->close();

 log_user_action($conn, null, "Failed login: email not found ($email)", $_SERVER['PHP_SELF']);
        echo "Invalid login credentials.";
        exit();
    }

    if (!empty($existing_session_id)) {
        echo "This account is already logged in on another device or browser. Please log out first.";
        exit();
    }

    session_regenerate_id(true);
    $currentSessionId = session_id();

    $updateSession = $conn->prepare("UPDATE users SET current_session_id = ? WHERE user_id = ?");
    $updateSession->bind_param("si", $currentSessionId, $user_id);
    $updateSession->execute();

    $otp = rand(100000, 999999);

    $_SESSION['mfa_user_id'] = $user_id;
    $_SESSION['mfa_username'] = $username;
    $_SESSION['mfa_email'] = $email;
    $_SESSION['mfa_otp'] = $otp;
    $_SESSION['mfa_expires'] = time() + 300;
    $_SESSION['is_admin'] = $is_admin;

    if ($is_admin) {
        $_SESSION['admin_logged_in'] = true;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dprince.ismail@gmail.com';
        $mail->Password = 'gipy tzuh nmqy izgn';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('dprince.ismail@gmail.com', 'Lan Bakery');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "
            <p>Hello <strong>$username</strong>,</p>
            <p>Your OTP code is: <strong>$otp</strong></p>
            <p>This code is valid for 5 minutes.</p>
            <p>--<br>Lan Bakery</p>
        ";

    log_user_action($conn, $user_id, "Successful login", $_SERVER['PHP_SELF']);

        $mail->send();
        header("Location: verify_otp.php");
        exit();
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        echo "Failed to send OTP. Please try again.";
    }
}

$conn->close();
?>
