<?php
// Toyyibpay callback to confirm payment
$servername = "...";
$db_username = "...";
$db_password = "...";
$dbname = "...";
$port = ...6;
$pdo = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $db_username, $db_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bill_code = $_POST['billcode'] ?? null;
    $transaction_id = $_POST['transaction_id'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($bill_code && $transaction_id && $status !== null) {
        if ($status == '1') {
            // Update orders where status is still pending
            $stmt = $pdo->prepare("UPDATE orders SET status = 'paid', updated_at = CURRENT_TIMESTAMP() WHERE status = 'pending'");
            $stmt->execute();

            file_put_contents('payment_logs.txt', "✅ Payment success - BillCode: $bill_code, Transaction: $transaction_id\n", FILE_APPEND);
        } else {
            file_put_contents('payment_logs.txt', "❌ Payment failed - BillCode: $bill_code\n", FILE_APPEND);
        }
    } else {
        file_put_contents('payment_logs.txt', "⚠️ Invalid data received\n", FILE_APPEND);
    }
} else {
    file_put_contents('payment_logs.txt', "⚠️ Invalid method used\n", FILE_APPEND);
}

http_response_code(200);
