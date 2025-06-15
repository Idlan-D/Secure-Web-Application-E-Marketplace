<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
    $user_otp = trim($_POST['otp']);

    if (!isset($_SESSION['mfa_otp']) || !isset($_SESSION['mfa_expires'])) {
        echo "Session expired. Please log in again.";
        exit();
    }

    $stored_otp = $_SESSION['mfa_otp'];
    $expires_at = $_SESSION['mfa_expires'];

    if (time() > $expires_at) {
        echo "OTP expired. Please log in again.";
        session_destroy();
        exit();
    }

if ($user_otp == $stored_otp) {
    // Clear OTP session variables
    unset($_SESSION['mfa_otp']);
    unset($_SESSION['mfa_expires']);

    // Complete the login process
    $_SESSION['user_id'] = $_SESSION['mfa_user_id'];
    $_SESSION['username'] = $_SESSION['mfa_username'];
    $_SESSION['email'] = $_SESSION['mfa_email'];

    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
    } else {
        header("Location: index.html");
    }
    exit();

    } else {
        echo "Invalid OTP. Please try again.";
    }
}
?>


<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- basic -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- mobile metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <!-- site metas -->
    <title>Lan Bakery</title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- bootstrap css -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <!-- style css -->
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <!-- Responsive-->
    <link rel="stylesheet" href="css/responsive.css">
    <!-- fevicon -->
    <link rel="icon" href="images/fevicon.png" type="image/gif" />
    <!-- Scrollbar Custom CSS -->
    <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
    <!-- Tweaks for older IEs-->
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
    <!-- fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
    <!-- font awesome -->
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <!--  -->
    <!-- owl stylesheets -->
    <link href="https://fonts.googleapis.com/css?family=Great+Vibes|Poppins:400,700&display=swap&subset=latin-ext" rel="stylesheet">
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

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email OTP Verification</title>
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        /* Button Styling */
        button {
            background-color: #ff8c00; /* Orange color */
            color: white; /* White text */
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            text-transform: uppercase;
            display: block;
            margin: 20px auto 0; /* Auto centers horizontally */
        }

        button:hover {
            background-color: #e07b00; /* Darker orange on hover */
        }

        /* Container Styling */
        .verification-container {
            text-align: center; /* Center-align content */
            position: relative;
            margin: 800px auto;
            max-width: 400px;
            border: 1px solid #ddd;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        h2 {
            margin-bottom: 20px;
            font-family: Arial, sans-serif;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container verification-container">
        <h2>EMAIL OTP VERIFICATION</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" id="otp" name="otp" placeholder="Enter OTP" required>
            <button type="submit" name="verify_otp">Verify OTP</button>
        </form>
    </div>
</body>
</html>
