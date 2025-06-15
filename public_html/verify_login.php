<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_code = trim($_POST['verification_code']);

    // Check if session contains the verification code
    if (isset($_SESSION['verification_code']) && $input_code == $_SESSION['verification_code']) {
        // Code is valid
        $_SESSION['verified'] = true;

        // Redirect to dashboard or another secure page
        header("Location: index.html");
        exit();
    } else {
        echo "<script>alert('Invalid verification code.');</script>"; // Alert for invalid code
    }
}

// Get the user's email from session
$user_email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lan Bakery</title>
    <link rel="icon" type="image/png" href="images/icons/favicon.ico"/>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic js-tilt" data-tilt>
                    <img src="images/footer-logo.png" alt="IMG">
                </div>
                <form class="login100-form validate-form" action="verify_login.php" method="post"> <!-- Action changed -->
                    <span class="login100-form-title">
                        Verify with Email Code
                    </span>
                    <p style="text-align: center; color: #666; margin-top: 10px; padding-bottom: 15px;"> <!-- Added padding-bottom -->
                        Use the code sent to <strong><?php echo htmlspecialchars($user_email); ?></strong>. <!-- Email in bold -->
                    </p>
                    <div class="wrap-input100 validate-input" data-validate="Verification code is required"> <!-- Updated validation message -->
                        <input class="input100" type="text" name="verification_code" placeholder="Verification Code" required> <!-- Input for verification code -->
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-code" aria-hidden="true"></i> <!-- Changed icon to reflect code input -->
                        </span>
                    </div>
                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn">
                            Verify
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- jQuery -->
    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <!-- Tilt JS -->
    <script src="vendor/tilt/tilt.jquery.min.js"></script>
    <!-- Custom JS -->
    <script src="js/main.js"></script>
</body>
</html>
