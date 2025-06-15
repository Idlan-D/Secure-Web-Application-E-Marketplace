<?php
session_start();

// Database configuration
$servername = "127.0.0.1";
$db_username = "u875650075_idlan";
$db_password = "Idlan@123";
$dbname = "u875650075_idlan_database";
$port = 3306;

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

require_once 'logger.php';


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle delete account request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["error" => "Invalid CSRF token"]);
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

log_user_action($conn, $user_id, "User deleted their account", $_SERVER['PHP_SELF']);


    session_destroy();
    echo json_encode(["success" => "Account deleted"]);
    exit();
}

function validate_name($name) {
    if (strlen($name) > 50) {
        return false;
    }
    return preg_match("/^[a-zA-Z-' ]+$/", $name);
}

function validate_username($username) {
    if (strlen($username) < 3 || strlen($username) > 20) {
        return false;
    }
    return preg_match("/^[a-zA-Z0-9_]+$/", $username);
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_account'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["error" => "Invalid CSRF token"]);
        exit();
    }

    function validate_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    $updates = [];
    $allowed_fields = ['first_name', 'last_name', 'username'];
    foreach ($allowed_fields as $field) {
    if (!empty($_POST[$field])) {
        $value = trim($_POST[$field]);

        if ($field == 'first_name' || $field == 'last_name') {
            if (!validate_name($value)) {
                echo json_encode(["error" => ucfirst(str_replace('_', ' ', $field)) . " is invalid or too long"]);
                exit();
            }
        }

        if ($field == 'username') {
            if (!validate_username($value)) {
                echo json_encode(["error" => "Username must be 3-20 characters, letters, numbers or underscores only"]);
                exit();
            }
        }

        $updates[$field] = $value;
    }
}


  //  if (!empty($_POST['new_password'])) {
      //  $current_password = $_POST['current_password'] ?? '';
      //  $new_password = $_POST['new_password'];

      //  if (strlen($new_password) < 6) {
       //     exit();
     //   }

      //  $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
      //  $stmt->bind_param("i", $user_id);
      //  $stmt->execute();
      //  $stmt->bind_result($hashed_password);
      //  $stmt->fetch();
      //  $stmt->close();

      //  if (!password_verify($current_password, $hashed_password)) {
      //      echo json_encode(["error" => "Current password is incorrect"]);
      //      exit();
     //   }

     //   $updates['password'] = password_hash($new_password, PASSWORD_DEFAULT);
     //   log_user_action($conn, $user_id, "Changed password", $_SERVER['PHP_SELF']);

   // }

foreach ($updates as $field => $value) {
    // Fetch current value for comparison
    $stmt = $conn->prepare("SELECT $field FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($current_value);
    $stmt->fetch();
    $stmt->close();

    if ($current_value !== $value) {
        $stmt = $conn->prepare("UPDATE users SET $field = ? WHERE user_id = ?");
        $stmt->bind_param("si", $value, $user_id);
        if (!$stmt->execute()) {
            echo json_encode(["error" => "Failed to update $field"]);
            exit();
        }

        log_user_action($conn, $user_id, "Updated $field from '$current_value' to '$value'", $_SERVER['PHP_SELF']);
        $stmt->close();
    }
}


    echo json_encode(["success" => "Profile updated successfully"]);
    $conn->close();
    exit();
}

// Retrieve user details
$stmt = $conn->prepare("SELECT username, email, first_name, last_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $username = htmlspecialchars($row['username']);
    $email = htmlspecialchars($row['email']);
    $first_name = htmlspecialchars($row['first_name']);
    $last_name = htmlspecialchars($row['last_name']);
} else {
    echo "User not found.";
    exit();
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lan Bakery</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="icon" href="images/fevicon.png" type="image/gif" />
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">

    <style>
        .banner_section {
            position: relative;
            z-index: 1;
            height: auto !important;
            overflow: visible !important;
        }
        .order-history-table th {
            background-color: #343a40;
            color: white;
            text-align: center;
        }
        .order-history-table td {
            vertical-align: middle;
            text-align: center;
        }
        .order-history-table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .profile-container .card {
            border-radius: 8px;
            background-color: #ffffff;
        }
        .profile-container .form-label {
            font-weight: 500;
        }
        .profile-container .btn-outline-secondary {
            min-width: 80px;
        }
    </style>
</head>
<body>
    <div class="banner_bg_main">
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
        <div class="banner_section layout_padding">
            <div class="container">
                <div class="buynow_bt"><a href="menu.php">Menu</a></div>
            </div>
        </div>
    </div>
    
  <!-- Profile Edit Section -->
 <div class="container profile-container mt-5 mb-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title mb-4">User Profile</h3>
            <form id="profileForm">
    <input type="hidden" name="csrf_token" id="csrf_token">
                <div class="row mb-3">
                    <?php
                    $fields = [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'username' => $username
                    ];
                    foreach ($fields as $key => $val):
                    ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-capitalize"><?= str_replace("_", " ", $key) ?></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="<?= $key ?>_input" name="<?= $key ?>" value="<?= $val ?>" readonly>
                                <button type="button" class="btn btn-outline-secondary edit-btn" data-field="<?= $key ?>">Edit</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="email_input" value="<?= $email ?>" readonly>
                    </div>
                </div>

                

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" id="deleteAccountBtn" class="btn btn-outline-danger">Delete Account</button>
                    <div>
                        <a href="logout.php" class="btn btn-danger">Logout</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Order History Section -->
<div class="container mt-5 mb-5">
    <h3>Order History</h3>
    <div class="table-responsive">
        <table class="table table-striped table-bordered order-history-table">
            <thead class="thead-dark">
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch user order history
                $conn = new mysqli($servername, $db_username, $db_password, $dbname, $port);
                $stmt = $conn->prepare("SELECT order_id, order_date, product_name, quantity, price FROM orders WHERE user_id = ? ORDER BY order_date DESC");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $order_result = $stmt->get_result();

                if ($order_result && $order_result->num_rows > 0):
                    while ($order = $order_result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= htmlspecialchars($order['order_date']); ?></td>
                        <td><?= htmlspecialchars($order['product_name']); ?></td>
                        <td><?= htmlspecialchars($order['quantity']); ?></td>
                        <td>RM<?= number_format($order['price'], 2); ?></td>
                    </tr>
                <?php
                    endwhile;
                else:
                ?>
                    <tr>
                        <td colspan="5" class="text-center">No orders found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>



    <!-- Footer -->
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

    <div class="copyright_section">
        <div class="container">
            <p class="copyright_text">© 2024 All Rights Reserved. Design by Idlan Durrani</p>
        </div>
    </div>

 <!-- JAVASCRIPT INTEGRATION -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    // Load CSRF token
    $.get('csrf_token.php', function (token) {
        $('#csrf_token').val(token);
    });

    $('.edit-btn').click(function () {
        const field = $(this).data('field');
        $('#' + field + '_input').prop('readonly', false).focus();
    });

    $('#profileForm').on('submit', function (e) {
        e.preventDefault();

       

        $.ajax({
            url: 'profile.php',
            type: 'POST',
            data: $('#profileForm').serialize(),
            success: function (data) {
                try {
                    const res = JSON.parse(data);
                    if (res.success) {
                        alert(res.success);
                        location.reload();
                    } else {
                        alert(res.error);
                    }
                } catch {
                    alert('Server error.');
                }
            },
            error: function () {
                alert('Error saving changes.');
            }
        });
    });

    $('#deleteAccountBtn').click(function () {
        if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
            $.get('csrf_token.php', function (token) {
                $.post('profile.php', { delete_account: true, csrf_token: token }, function (data) {
                    try {
                        const res = JSON.parse(data);
                        if (res.success) {
                            alert(res.success);
                            window.location.href = 'login.html';
                        } else {
                            alert(res.error || 'Error deleting account.');
                        }
                    } catch {
                        alert('Server error.');
                    }
                });
            });
        }
    });
});
</script>