payment success
<?php
// payment_success.php

session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Success - Lan Bakery</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header Section -->
    <div class="banner_bg_main">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
<div style="text-align: center;">
    <img src="images/footer-logo-1.png" alt="Lan Bakery" width="150">
</div>
                    <nav class="navbar navbar-expand-lg navbar-light">
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav ml-auto">
                                <li class="nav-item"><a class="nav-link" href="index.php"></a></li>
                                <li class="nav-item"><a class="nav-link" href="menu.php"></a></li>
                                <li class="nav-item"><a class="nav-link" href="cart.php"></a></li>
                                <li class="nav-item"><a class="nav-link" href="contact.php"></a></li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Success Section Start -->
    <div class="container mt-5">
        <h1 class="text-center text-success">Thank you for your purchase!</h1>
        <p class="text-center">Your order has been successfully placed and payment received.</p>
        <p class="text-center">We will start preparing your order shortly. You will receive a confirmation email with the order details.</p>
        <div class="text-center">
            <a href="index.html" class="btn btn-primary">Return to Home</a>
        </div>
    </div>
    <!-- Payment Success Section End -->

    <!-- Footer Section -->
    <div class="footer_section layout_padding mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="footer_logo"><a href="index.php"><img src="images/footer-logo-1.png" alt="Lan Bakery" /></a></div>
                    <p class="text-center">© 2024 Lan Bakery. All Rights Reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
