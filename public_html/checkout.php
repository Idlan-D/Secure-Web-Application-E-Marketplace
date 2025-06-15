<?php
session_start();

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection
$servername = "127.0.0.1";
$db_username = "u875650075_idlan";
$db_password = "Idlan@123";
$dbname = "u875650075_idlan_database";
$port = 3306;

try {
    $pdo = new PDO("mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

$payment_gateway_active = true;

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Error: Invalid CSRF token');
    }

    // Sanitize inputs
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $address = filter_var(trim($_POST['address']), FILTER_SANITIZE_STRING);
    $city = filter_var(trim($_POST['city']), FILTER_SANITIZE_STRING);
    $state = filter_var(trim($_POST['state']), FILTER_SANITIZE_STRING);
    $postal_code = filter_var(trim($_POST['postal_code']), FILTER_SANITIZE_STRING);
    $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

// === Add digit-only validation here ===
if (!ctype_digit($postal_code)) {
    $errors[] = "Postal code must contain digits only.";
}

if (!ctype_digit($phone)) {
    $errors[] = "Phone number must contain digits only.";
}

    // Validate inputs
    if (empty($name)) $errors[] = "Name is required.";
    if (empty($address)) $errors[] = "Address is required.";
    if (empty($city)) $errors[] = "City is required.";
    if (empty($state)) $errors[] = "State is required.";
    if (empty($postal_code)) $errors[] = "Postal code is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($email)) $errors[] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";

    if (empty($errors)) {
        // Calculate total price
        $total_price = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total_price += $item['price'] * $item['quantity'];
        }

        $user_id = $_SESSION['user_id'];

        // Insert each item as a separate order row
       $stmt = $pdo->prepare("INSERT INTO orders (user_id, quantity, status, product_name, price, image_url, full_name, address, city, state, postal_code, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

try {
    $pdo->beginTransaction();
    foreach ($_SESSION['cart'] as $item) {
        $stmt->execute([
            $user_id,
            $item['quantity'],
            'pending',
            $item['product_name'],
            $item['price'],
            $item['image_url'],
            $name,
            $address,
            $city,
            $state,
            $postal_code,
            $phone
        ]);
    }
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Failed to place order: " . htmlspecialchars($e->getMessage()));
}

        if ($payment_gateway_active) {
            $api_key = "wi9rjbdu-e46l-h8hd-gsjy-zrdy1mxp5l8i";
            $category_code = "q9gkxge1";
            $bill_reference = 'Order_' . uniqid();

            $bill_data = [
                'userSecretKey' => $api_key,
                'categoryCode' => $category_code,
                'billName' => 'Lan Bakery Order',
                'billDescription' => 'Order for ' . $name,
                'billPriceSetting' => 1,
                'billPayorInfo' => 1,
                'billAmount' => $total_price * 100,
                'billReturnUrl' => 'http://lanbakery.shop/payment_success.php',
                'billCallbackUrl' => 'http://lanbakery.shop/payment_callback.php',
                'billExternalReferenceNo' => $bill_reference,
                'billTo' => $name,
                'billEmail' => $email,
                'billPhone' => $phone,
                'billSplitPayment' => 0,
                'billPaymentChannel' => 0,
                'billContentEmail' => 'Thank you for your purchase!',
                'billChargeToCustomer' => 1
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://toyyibpay.com/index.php/api/createBill');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($bill_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);

            if ($response === false) {
                echo 'Curl error: ' . htmlspecialchars(curl_error($ch));
                curl_close($ch);
                exit;
            }

            curl_close($ch);
            $response_data = json_decode($response, true);

            if (isset($response_data[0]['BillCode'])) {
                $_SESSION['cart'] = []; // Clear cart after starting payment
                
                    // LOG HERE
    try {
        $log_stmt = $pdo->prepare("
            INSERT INTO user_logs (user_id, action, page, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $log_stmt->execute([
            $user_id,
            'Placed order and redirected to Toyyibpay',
            'checkout.php',
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT']
        ]);
    } catch (Exception $e) {
        die("Logging failed: " . htmlspecialchars($e->getMessage()));
    }
                
                header("Location: https://toyyibpay.com/" . htmlspecialchars($response_data[0]['BillCode']));
                exit;
            } else {
                echo "<div class='alert alert-danger'>Failed to create bill.</div>";
                echo "<pre>";
                echo htmlspecialchars(print_r($response_data, true));
                echo "</pre>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Checkout - Lan Bakery</title>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <!-- banner bg main start -->
    <div class="banner_bg_main">
        <!-- header top section start -->
        <div class="container">
            <div class="header_section_top">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="custom_menu">
                            <ul>
                                <li><a href="about.html">About Us</a></li>
                                <li><a href="login.html">Login</a></li>
                                <li><a href="contact.html">Contact</a></li>
                                <li><a href="menu.php">Menu</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- header top section start -->

        <!-- header section start -->
        <div class="header_section">
            <div class="container">
                <div class="containt_main">
                    <div id="mySidenav" class="sidenav">
                        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
                        <a href="index.html">Lan Bakery</a>
                        <a href="login.html">Login</a>
                        <a href="contact.html">Contact</a>
                        <a href="menu.php">Menu</a>
                    </div>
                    <span class="toggle_icon" onclick="openNav()"><img src="images/toggle-icon.png"></span>
                    <div class="main">
                    </div>
                    <div class="header_box">
                        <div class="login_menu">
                            <ul>
                                <li><a href="cart.php">
                                    <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                                    <span class="padding_10">Cart</span></a>
                                </li>
                                <li><a href="profile.php">
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                    <span class="padding_10">Profile</span></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- header section end -->
        <!-- banner section start -->
        <div class="banner_section layout_padding">
            <div class="container">               
                <div class="buynow_bt"><a href="menu.php">Menu</a></div>
            </div>
        </div>
        <!-- banner section end -->
    </div>
    <!-- banner bg main end -->
    

    <!-- Checkout Section Start -->
   <div class="container mt-5">
        <h1 class="text-center">Checkout</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="checkout.php" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required value="<?= isset($name) ? htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" class="form-control" id="address" name="address" required value="<?= isset($address) ? htmlspecialchars($address, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" class="form-control" id="city" name="city" required value="<?= isset($city) ? htmlspecialchars($city, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>
            <div class="form-group">
                <label for="state">State</label>
                <input type="text" class="form-control" id="state" name="state" required value="<?= isset($state) ? htmlspecialchars($state, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>
            <div class="form-group">
                <label for="postal_code">Postal Code</label>
                <input type="text" class="form-control" id="postal_code" name="postal_code" required value="<?= isset($postal_code) ? htmlspecialchars($postal_code, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" required value="<?= isset($phone) ? htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required value="<?= isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : '' ?>">
            </div>

            <h2>Order Summary</h2>
            <table class="table table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_price = 0; // Initialize total price
                    foreach ($_SESSION['cart'] as $item) {
                        $item_total_price = $item['price'] * $item['quantity'];
                        $total_price += $item_total_price;
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>RM" . htmlspecialchars(number_format($item['price'], 2), ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . (int)$item['quantity'] . "</td>";
                        echo "<td>RM" . htmlspecialchars(number_format($item_total_price, 2), ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "</tr>";
                    }
                    ?>
                    <tr>
                        <td colspan="3"><strong>Total</strong></td>
                        <td><strong>RM<?= htmlspecialchars(number_format($total_price, 2), ENT_QUOTES, 'UTF-8') ?></strong></td>
                    </tr>
                </tbody>
            </table>

            <button type="submit" class="btn btn-success">Place Order</button>
        </form>
    </div>
    <!-- Checkout Section End -->

      <!-- footer section start -->
      <div class="footer_section layout_padding">
         <div class="container">
            <div class="footer_logo"><a href="index.html"><img src="images/footer-logo-1.png"></a></div>
            <div class="input_bt">
               <input type="text" class="mail_bt" placeholder="Email" name="Email">
               <span class="subscribe_bt" id="basic-addon2"><a href="#">Join Us</a></span>
            </div>
            <div class="footer_menu">
               <ul>
                  <li><a href="about.html">About us</a></li>
                  <li><a href="login.html">Log in</a></li>
                  <li><a href="contact.html">Contact</a></li>
                  <li><a href="menu.php">Menu</a></li>
               </ul>
            </div>
            <div class="location_main">Contact Number : <a href="#">+011 23771211</a></div>
         </div>
      </div>
      <!-- footer section end -->
      <!-- copyright section start -->
      <div class="copyright_section">
         <div class="container">
            <p class="copyright_text">© 2024 All Rights Reserved. Design by Idlan Durrani</a></p>
         </div>
      </div>
      <!-- copyright section end -->

    <!-- Scripts -->
 <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>