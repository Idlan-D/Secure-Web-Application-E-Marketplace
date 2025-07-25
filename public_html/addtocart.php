<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["product_name"], $_POST["price"], $_POST["image_url"], $_POST["quantity"])) {

    if (isset($_SESSION["user_id"])) {
        $user_id = $_SESSION["user_id"];
    } else {
        echo json_encode(array("success" => false, "message" => "User not logged in."));
        exit;
    }

    // Prepare the product data
    $product_name = $_POST["product_name"];
    $price = $_POST["price"];
    $image_url = $_POST["image_url"];
    $quantity = $_POST["quantity"];

    // Add to session cart (initialize if not set)
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $cart_item = array(
        "product_name" => $product_name,
        "price" => $price,
        "image_url" => $image_url,
        "quantity" => $quantity
    );

    $_SESSION['cart'][] = $cart_item;

    // ✅ Log the action into the database
    $conn = new mysqli(...);
    if ($conn->connect_error) {
        echo json_encode(array("success" => false, "message" => "Database connection failed."));
        exit;
    }

    $action = "Added to cart: $quantity x $product_name (RM$price)";
    $page = "menu.php";
    $ip = $_SERVER['REMOTE_ADDR'];

    $stmt = $conn->prepare("INSERT INTO user_logs (user_id, action, page, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $page, $ip);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // ✅ Success response
    echo json_encode(array("success" => true, "message" => "Product added to cart successfully."));
} else {
    echo json_encode(array("success" => false, "message" => "Invalid request."));
}
?>
