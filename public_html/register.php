<?php
session_start();

// Include Composer's autoloader
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Database configuration
$servername = "127.0.0.1";
$db_username = "u875650075_idlan";  // use your database username
$db_password = "Idlan@123";      // use your database password
$dbname = "u875650075_idlan_database"; // use your database name
$port = 3306;           // specify the port number

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error)); // Avoid revealing database errors
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data and sanitize it
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['pass'];
    $confirm_password = $_POST['confirm_pass'];

    // Initialize error messages
    $errors = [];

    // Validate first name (letters only, max length 30)
    if (empty($first_name) || !preg_match("/^[A-Za-z\s]+$/", $first_name) || strlen($first_name) > 30) {
        $errors['first_name'] = "First name must contain only letters and spaces, and be less than 30 characters.";
    }

    // Validate last name (letters only, max length 30)
    if (empty($last_name) || !preg_match("/^[A-Za-z\s]+$/", $last_name) || strlen($last_name) > 30) {
        $errors['last_name'] = "Last name must contain only letters and spaces, and be less than 30 characters.";
    }

    // Validate username (alphanumeric/underscores, 4-20 characters)
    if (empty($username) || !preg_match("/^[a-zA-Z0-9_]{4,20}$/", $username)) {
        $errors['username'] = "Username must be between 4 and 20 characters, and contain only letters, numbers, and underscores.";
    }

    // Validate email format with stricter regex
    if (empty($email) || !preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/", $email) || strlen($email) > 50) {
        $errors['email'] = "Please enter a valid email address.";
    }

    // Validate password (minimum 8 characters)
    if (empty($password) || strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters long.";
    }

    // Confirm password
    if (empty($confirm_password) || $password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Generate a random verification code
        $verification_code = generateVerificationCode();

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and bind statement
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, verification_code, verified) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("ssssss", $username, $email, $hashed_password, $first_name, $last_name, $verification_code);

        // Execute the statement
        if ($stmt->execute()) {
            // Store the email in session
            $_SESSION['email'] = $email;

            // Send the verification email
            sendVerificationEmail($email, $verification_code, $first_name);

            // Redirect to verification page
            header("Location: verify.php?email=" . urlencode($email));
            exit();
        } else {
            echo "An error occurred while registering. Please try again later."; // Generic error message
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the connection
$conn->close();

// Function to generate a random verification code
function generateVerificationCode() {
    return bin2hex(random_bytes(4)); // 8 characters (4 bytes)
}

// Function to send the verification code via email
function sendVerificationEmail($email, $verification_code, $first_name) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dprince.ismail@gmail.com'; // Your Gmail username
        $mail->Password = 'gipy tzuh nmqy izgn';   // Your Gmail password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->SMTPAutoTLS = false;  // Disable automatic TLS encryption if needed

        // Recipients
        $mail->setFrom('dprince.ismail@gmail.com', 'Lan Bakery');
        $mail->addAddress($email);

        // Content (with XSS prevention using htmlspecialchars)
        $mail->isHTML(true);
        $mail->Subject = 'Account Verification';
        $mail->Body = '
            <p>Dear ' . htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8') . ',</p>
            <p>Thank you for registering with us. Please use the following verification code to verify your account:</p>
            <h2>' . htmlspecialchars($verification_code, ENT_QUOTES, 'UTF-8') . '</h2>
            <p>Enter this code in the verification page to complete your registration.</p>
            <p>Your favourite bakery store,<br>Lan Bakery</p>
        ';
        $mail->send();
    } catch (Exception $e) {
        // Log error (optional)
    }
}
?>