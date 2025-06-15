<?php
function log_user_action($conn, $user_id, $action, $page) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $stmt = $conn->prepare("INSERT INTO user_logs (user_id, action, page, ip_address, user_agent, log_time) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $user_id, $action, $page, $ip, $user_agent);
    $stmt->execute();
    $stmt->close();
}
?>