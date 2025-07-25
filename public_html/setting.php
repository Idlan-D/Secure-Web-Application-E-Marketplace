<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
$servername = "...";
$db_username = "...";
$db_password = "...";
$dbname = "...";
$port = ...;

// Include Composer's autoload file
require_once '/home/u428930445/vendor/autoload.php'; // Adjust path if needed

$conn = new mysqli($servername, $db_username, $db_password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Simulate a logged-in user for testing (Replace with your session logic)
$_SESSION['user_id'] = 723; // Replace with an actual user ID from your database

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["error" => "User not logged in."]));
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enable_totp'])) {
        // Enable TOTP
        $g = new PHPGangsta_GoogleAuthenticator();
        $secret = $g->createSecret();

        $stmt = $conn->prepare("UPDATE users SET totp_secret = ?, two_factor_enabled = 1 WHERE user_id = ?");
        $stmt->bind_param("si", $secret, $user_id);

        if ($stmt->execute()) {
            $qrCodeUrl = $g->getQRCodeGoogleUrl("user_$user_id", $secret, 'NorlizaCookies');
            error_log("TOTP Enabled: user_id $user_id, secret $secret");
            echo json_encode([
                "success" => "TOTP enabled successfully.",
                "qrCodeUrl" => $qrCodeUrl
            ]);
        } else {
            error_log("Enable TOTP Error: " . $stmt->error);
            echo json_encode(["error" => "Error enabling TOTP."]);
        }
        $stmt->close();
    } elseif (isset($_POST['disable_totp'])) {
        // Disable TOTP
        $stmt = $conn->prepare("UPDATE users SET totp_secret = NULL, two_factor_enabled = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            error_log("TOTP Disabled: user_id $user_id");
            echo json_encode(["success" => "TOTP disabled successfully."]);
        } else {
            error_log("Disable TOTP Error: " . $stmt->error);
            echo json_encode(["error" => "Error disabling TOTP."]);
        }
        $stmt->close();
    }

    // Validate database after action
    $result = $conn->query("SELECT user_id, totp_secret, two_factor_enabled FROM users WHERE user_id = $user_id");
    if ($result && $row = $result->fetch_assoc()) {
        error_log("Post-Action Check: " . json_encode($row));
        echo json_encode(["userData" => $row]);
    }
    $conn->close();
    exit();
}

// Default response if accessed without POST
echo json_encode(["error" => "Invalid request."]);
?>
