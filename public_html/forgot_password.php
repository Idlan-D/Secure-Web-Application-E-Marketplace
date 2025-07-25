<?php
require 'vendor/autoload.php';
session_start();

$servername = "...";
$db_username = "...";
$db_password = "...";
$dbname = "...";
$port = ...;

$conn = new mysqli($servername, $db_username, $db_password, $dbname, $port);
if ($conn->connect_error) die("DB Error");

$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

// Default message
$message = "If the email you entered is registered, a password reset link has been sent to it. Please check your inbox or spam folder.";

// 1. Clean old used tokens older than 1 minute and expired tokens
$cleanup = $conn->prepare("
  DELETE FROM password_resets 
  WHERE email = ? 
    AND ( (used = 1 AND created_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)) 
          OR expires_at < NOW() )
");
$cleanup->bind_param("s", $email);
$cleanup->execute();

// 2. Check if a recent valid token exists (created within last 5 minute)
$check = $conn->prepare("
  SELECT id FROM password_resets 
  WHERE email = ? AND used = 0 AND expires_at > NOW() AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
  LIMIT 1
");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $message = "Please wait a minute before requesting a new password reset link.";
} else {
    // 3. Proceed if email exists in users table
    $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        $token = bin2hex(random_bytes(32));
        $token_hash = password_hash($token, PASSWORD_DEFAULT);
        $expires = date("Y-m-d H:i:s", time() + 3600); // 1 hour expiry
        $created_at = date("Y-m-d H:i:s");

        // Insert new token
        $insert = $conn->prepare("INSERT INTO password_resets (email, token_hash, expires_at, created_at) VALUES (?, ?, ?, ?)");
        $insert->bind_param("ssss", $email, $token_hash, $expires, $created_at);
        $insert->execute();

        // Send reset email
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
            $mail->isHTML(true);
            $mail->Subject = 'Reset Your Password';
            $mail->Body = "
                <p>Hello {$user['username']},</p>
                <p>Click below to reset your password:</p>
                <a href='https://lanbakery.shop/reset_password.php?email=" . urlencode($email) . "&token=$token'>Reset Password</a>
                <p>This link expires in 1 hour.</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            $message = "Something went wrong while sending the reset email. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset Requested</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f9fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        h2 {
            color: #333;
        }

        p {
            color: #555;
            line-height: 1.5;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Password Reset Link Status</h2>
        <p><?php echo htmlspecialchars($message); ?></p>
        <p>After verifying your identity and resetting your password, click the button below to return to the login page.</p>
        <a href="login.html" class="btn">Go to Login</a>
    </div>
</body>
</html>
