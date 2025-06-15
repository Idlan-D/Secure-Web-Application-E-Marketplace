<?php
session_start();

// Database connection
$servername = "127.0.0.1";
$username = "u875650075_idlan";
$password = "Idlan@123";
$dbname = "u875650075_idlan_database";
$port = 3306;

try {
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

function logAction($conn, $user_id, $action, $page = 'cart.php') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO user_logs (user_id, action, page, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $page, $ip]);
}

// Handle quantity update and remove actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : null; // For logging

    if (isset($_POST['action']) && $_POST['action'] == 'update') {
        $product_name = $_POST['product_name'];
        $new_quantity = $_POST['quantity'];

        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_name'] == $product_name) {
                $old_quantity = $item['quantity'];
                $item['quantity'] = $new_quantity;

                // ✅ Log the update
                if ($user_id !== null) {
                    $action = "Updated cart: $product_name from $old_quantity to $new_quantity";
                    logAction($conn, $user_id, $action);
                }

                break;
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] == 'remove') {
        $product_name = $_POST['product_name'];

        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['product_name'] == $product_name) {
                // ✅ Log the removal before deleting
                if ($user_id !== null) {
                    $action = "Removed from cart: $product_name (Quantity: {$item['quantity']})";
                    logAction($conn, $user_id, $action);
                }

                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
                break;
            }
        }
    }

    header("Location: cart.php");
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Cart - Lan Bakery</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
     <style>
        /* Fix overlapping issue */
        .header_box {
            position: relative;
            z-index: 999;
        }

        .login_menu ul li {
            position: relative;
            z-index: 1000;
        }

        .banner_section,
        .container {
            position: relative;
            z-index: 1;
        }
    </style>
    <style>
    .header_section {
        position: relative;
        z-index: 1000;
    }

    .banner_section {
        position: relative;
        z-index: 1;
    }
</style>
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
                                <li><a href="login.html">Log in</a></li>
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
                        <a href="login.html">Log in</a>
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

    <!-- Cart Section Start -->
    <div class="container">
        <h1 class="text-center mt-5">Your Cart</h1>
        <?php
        // Check if the cart is set in the session and is not empty
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            echo "<table class='table table-bordered mt-4'>";
            echo "<thead class='thead-dark'>";
            echo "<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total Price</th><th>Image</th><th>Action</th></tr>";
            echo "</thead>";
            echo "<tbody>";

            $total_price = 0; // Initialize total price

            // Loop through the cart session
            foreach ($_SESSION['cart'] as $item) {
                $item_total_price = $item['price'] * $item['quantity']; // Calculate total price for the item
                $total_price += $item_total_price; // Add to total cart price

                echo "<tr>";
                echo "<td>{$item['product_name']}</td>";
                echo "<td>RM{$item['price']}</td>";
                echo "<td>";
                echo "<form method='POST' action='cart.php' style='display: inline;'>";
                echo "<input type='hidden' name='product_name' value='{$item['product_name']}'>";
                echo "<input type='hidden' name='action' value='update'>";
                echo "<input type='number' name='quantity' value='{$item['quantity']}' min='1' class='form-control' style='width: 80px; display: inline-block;'>";
                echo "<button type='submit' class='btn btn-primary btn-sm'>Update</button>";
                echo "</form>";
                echo "</td>";
                echo "<td>RM{$item_total_price}</td>";
                echo "<td><img src='{$item['image_url']}' width='100'></td>";
                echo "<td>";
                echo "<form method='POST' action='cart.php' style='display: inline;'>";
                echo "<input type='hidden' name='product_name' value='{$item['product_name']}'>";
                echo "<input type='hidden' name='action' value='remove'>";
                echo "<button type='submit' class='btn btn-danger btn-sm'>Remove</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }

            echo "<tr>";
            echo "<td colspan='3'><strong>Total</strong></td>";
            echo "<td colspan='2'><strong>RM{$total_price}</strong></td>";
            echo "<td></td>";
            echo "</tr>";
            echo "</tbody>";
            echo "</table>";

            echo "<div class='text-right'>";
            echo "<a href='checkout.php' class='btn btn-success'>Proceed To Checkout</a>";
            echo "<a href='menu.php' class='btn btn-primary ml-2'>Continue Shopping</a>";
            echo "</div>";
        } else {
            // If the cart is empty, show a message
            echo "<p class='text-center'>Your cart is empty.</p>";
        }
        ?>
    </div>
    <!-- Cart Section End -->

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
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>

