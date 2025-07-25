<?php
session_start();

// Check if admin is logged in and handle session cleanup
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    // Handle admin logout
    unset($_SESSION['admin_logged_in']);
    // Ensure that the session is cleared in the database as well for admin
    if (isset($_SESSION['mfa_user_id'])) {
        $user_id = $_SESSION['mfa_user_id'];

        // Connect to DB
        $conn = new mysqli("...", "...", "...", "...", ...);
        if (!$conn->connect_error) {
            // Clear current session ID in DB for admin
            $stmt = $conn->prepare("UPDATE users SET current_session_id = NULL WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            $conn->close();
        }
    }
} else {
    // Handle normal user logout (if needed, ensure it's cleared for regular users as well)
    if (isset($_SESSION['mfa_user_id'])) {
        $user_id = $_SESSION['mfa_user_id'];

        // Connect to DB
        $conn = new mysqli("...", "...", "...", "...", ...);
        if (!$conn->connect_error) {
            // Clear current session ID in DB for regular user
            $stmt = $conn->prepare("UPDATE users SET current_session_id = NULL WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            $conn->close();
        }
    }
}

// Unset and destroy the session
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.html");
exit();
?>
