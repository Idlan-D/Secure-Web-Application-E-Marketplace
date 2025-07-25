<?php
session_start();

// Ensure only admin can access
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli(...);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// <<< INSERT AJAX HANDLER HERE >>>
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mark_complete'])) {
    $order_id = intval($_POST['mark_complete']);

    $stmt = $conn->prepare("UPDATE orders SET status = 'Complete' WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update status']);
    }
    exit(); // Prevent further output
}

// Logout functionality
if (isset($_GET['logout'])) {
    // Clear current session ID in DB for admin
    if (isset($_SESSION['mfa_user_id'])) {
        $user_id = $_SESSION['mfa_user_id'];

        $stmt = $conn->prepare("UPDATE users SET current_session_id = NULL WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Unset and destroy the session
    session_unset();
    session_destroy();

    // Redirect to login page
    header("Location: login.html");
    exit();
}
// Update item (if form submitted)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_item'])) {
    $item_id = intval($_POST['item_id']);
    $new_name = trim($_POST['name']);
    $new_price = floatval($_POST['price']);

    $stmt = $conn->prepare("UPDATE items SET name = ?, price = ? WHERE item_id = ?");
    $stmt->bind_param("sdi", $new_name, $new_price, $item_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch dashboard stats
$logs_sql = "SELECT log_id, user_id, action, page, ip_address, user_agent, log_time FROM user_logs ORDER BY log_time DESC";
$logs_result = $conn->query($logs_sql);
$total_orders = $conn->query("SELECT COUNT(order_id) AS total_orders FROM orders")->fetch_assoc()['total_orders'];
$total_items = $conn->query("SELECT COUNT(item_id) AS total_items FROM items")->fetch_assoc()['total_items'];
$total_users = $conn->query("SELECT COUNT(user_id) AS total_users FROM users")->fetch_assoc()['total_users'];
$all_orders = $conn->query("
    SELECT o.order_id, o.user_id, u.username, o.order_date, o.status, o.product_name,o.address, o.price
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");
$users = $conn->query("SELECT username, email FROM users");

if (!$users) {
    die("Query failed: " . $conn->error);
}
$items = $conn->query("SELECT item_id, name, price, image_url FROM items");

// Logout functionality
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Lan Bakery</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="icon" href="images/fevicon.png" type="image/gif" />
    <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .editable-form input {
            width: 100%;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #dc3545;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .logout-btn:hover {
            background-color: #c82333;
        }
        .table-bordered th {
            background-color: #343a40;
            color: white;
            text-align: center;
        }
        .table-bordered td {
            vertical-align: middle;
            text-align: center;
        }
        .table-bordered tbody tr:hover {
            background-color: #f1f1f1;
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
                                <li><a href="about.html"></a></li>
                                <li><a href="login.html">Admin Dashboard</a></li>
                                <li><a href="contact.html"></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- header top section end -->

        <!-- header section start -->
        <div class="header_section">
            <div class="container">
                <div class="containt_main">
                    <div id="mySidenav" class="sidenav">
                        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
                        <a href="index.html">Lan Bakery</a>
                        <a href="login.html">Order</a>
                        <a href="contact.html">Pengguna</a>
                        <a href="menu.php">Item</a>
                    </div>
                    <span class="toggle_icon" onclick="openNav()"><img src="images/toggle-icon.png"></span>
                    <div class="main"></div>
<div class="header_box" style="position: absolute; top: 20px; right: 20px; z-index: 9999;">
    <a href="?logout=true" onclick="return confirm('Are you sure you want to log out?');" class="btn btn-danger">
        <i class="fa fa-sign-out" aria-hidden="true"></i> Logout
    </a>
</div>

                </div>
            </div>
        </div>
        <!-- header section end -->
    </div>

    <div class="dashboard-container">
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="container">
                    <h3 class="text-center">Admin Dashboard</h3>
                    <span class="welcome-message text-end">Welcome, Admin!</span>
                </div>
            </div>

            <div class="container">
                <!-- Dashboard Overview -->
                <div class="row dashboard-overview">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h4>Total Orders</h4>
                                <p><?= htmlspecialchars($total_orders); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h4>Total Products</h4>
                                <p><?= htmlspecialchars($total_items); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h4>Total Users</h4>
                                <p><?= htmlspecialchars($total_users); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registered Users -->
                <h4>Registered Users</h4>
                <table class="table table-bordered">
                    <thead><tr><th>Username</th><th>Email</th></tr></thead>
                    <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>

<!-- Product Management -->
<!-- Product Management -->
<h4 class="mt-5 mb-3">Manage Products</h4>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_item'])) {
    $item_id = intval($_POST['item_id']);
    $remove_item_data = isset($_POST['remove_item_data']) && $_POST['remove_item_data'] == '1';

    if ($remove_item_data) {
        // Clear all product info
        $stmt = $conn->prepare("UPDATE items SET name = '', price = 0, image_url = '' WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $stmt->close();
        echo '<div class="alert alert-warning">Product data has been cleared.</div>';
    } else {
        $new_name = trim($_POST['name']);
        $new_price = floatval($_POST['price']);

        if ($new_price <= 0) {
            echo '<div class="alert alert-danger">Price must be a positive number.</div>';
        } else {
            $image_url = null;
            if (!empty($_FILES['image']['name'])) {
                $target_dir = "uploads/";
                if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

                $filename = basename($_FILES["image"]["name"]);
                $target_file = $target_dir . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $filename);

                $check = getimagesize($_FILES["image"]["tmp_name"]);

                if ($check !== false && move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_url = $target_file;
                } else {
                    echo '<div class="alert alert-danger">Invalid image upload.</div>';
                }
            }

            if (!isset($image_url)) {
                $stmt = $conn->prepare("SELECT image_url FROM items WHERE item_id = ?");
                $stmt->bind_param("i", $item_id);
                $stmt->execute();
                $stmt->bind_result($existing_image);
                $stmt->fetch();
                $stmt->close();
                $image_url = $existing_image;
            }

            $stmt = $conn->prepare("UPDATE items SET name = ?, price = ?, image_url = ? WHERE item_id = ?");
            $stmt->bind_param("sdsi", $new_name, $new_price, $image_url, $item_id);
            if ($stmt->execute()) {
                echo '<div class="alert alert-success">Your product has been updated successfully.</div>';
            } else {
                echo '<div class="alert alert-danger">Failed to update the product.</div>';
            }
            $stmt->close();
        }
    }
}

$products = $conn->query("SELECT item_id, name, price, image_url FROM items");
?>

<div class="mb-4">
    <label for="productSelect" class="form-label fw-semibold">Select Product to Edit</label>
    <select id="productSelect" class="form-select form-select-lg">
        <option value="" selected disabled>-- Choose a product --</option>
        <?php while ($prod = $products->fetch_assoc()): ?>
            <option value="<?= $prod['item_id'] ?>"
                data-name="<?= htmlspecialchars($prod['name'], ENT_QUOTES) ?>"
                data-price="<?= htmlspecialchars($prod['price'], ENT_QUOTES) ?>"
                data-image="<?= htmlspecialchars($prod['image_url'], ENT_QUOTES) ?>">
                <?= htmlspecialchars($prod['name']) ?: "(No name)" ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<div id="editProductSection" class="card shadow-sm p-4 mb-5" style="display:none; max-width: 500px;">
    <form method="POST" enctype="multipart/form-data" class="editable-form">
        <input type="hidden" name="item_id" id="editItemId">
        <input type="hidden" name="remove_item_data" id="removeItemData" value="0">

        <div class="mb-4 text-center">
            <img id="currentImage" src="images/placeholder.png" alt="Product Image" 
                 class="img-fluid rounded border" style="max-height: 180px; object-fit: cover;">
        </div>

        <div class="mb-3">
            <label for="imageInput" class="form-label fw-semibold">Replace Image</label>
            <input type="file" name="image" id="imageInput" class="form-control">
        </div>

        <div class="mb-3">
            <label for="editName" class="form-label fw-semibold">Name</label>
            <input type="text" class="form-control form-control-lg" name="name" id="editName" required placeholder="Enter product name">
        </div>

        <div class="mb-3">
            <label for="editPrice" class="form-label fw-semibold">Price (RM)</label>
            <input type="number" step="0.01" min="0.01" class="form-control form-control-lg" name="price" id="editPrice" required placeholder="Enter price">
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <button type="submit" name="update_item" class="btn btn-success btn-lg">Save Changes</button>
            <button type="button" id="clearProductBtn" class="btn btn-outline-danger btn-lg">Clear Product Data</button>
        </div>
    </form>
</div>

<script>
    const productSelect = document.getElementById('productSelect');
    const editSection = document.getElementById('editProductSection');
    const editItemId = document.getElementById('editItemId');
    const editName = document.getElementById('editName');
    const editPrice = document.getElementById('editPrice');
    const currentImage = document.getElementById('currentImage');
    const imageInput = document.getElementById('imageInput');
    const clearProductBtn = document.getElementById('clearProductBtn');
    const removeItemDataInput = document.getElementById('removeItemData');

    productSelect.addEventListener('change', () => {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        if (!selectedOption.value) {
            editSection.style.display = 'none';
            return;
        }
        editSection.style.display = 'block';
        removeItemDataInput.value = '0'; // Reset clear flag on new selection
        editItemId.value = selectedOption.value;
        editName.value = selectedOption.dataset.name || '';
        editPrice.value = selectedOption.dataset.price || '';
        currentImage.src = selectedOption.dataset.image && selectedOption.dataset.image !== '' ? selectedOption.dataset.image : 'images/placeholder.png';
        imageInput.value = '';
    });

    clearProductBtn.addEventListener('click', () => {
        if (!confirm("Are you sure you want to clear all data for this product? This action cannot be undone.")) {
            return;
        }
        // Clear all input fields and image preview
        editName.value = '';
        editPrice.value = '';
        currentImage.src = 'images/placeholder.png';
        imageInput.value = '';
        removeItemDataInput.value = '1'; // Mark for clearing in POST
    });

    // Client-side price validation
    document.querySelector('form.editable-form').addEventListener('submit', function(e) {
        const priceVal = parseFloat(editPrice.value);
        if (removeItemDataInput.value !== '1') { // skip validation if clearing
            if (isNaN(priceVal) || priceVal <= 0) {
                e.preventDefault();
                alert('Please enter a valid positive number for price.');
                editPrice.focus();
            }
        }
    });
</script>




                <!-- Recent Orders -->
<!-- Recent Orders -->
<h4>Recent Orders</h4>
<table class="table table-bordered">
<thead>
<tr>
    <th>Order ID</th>
    <th>User ID</th>
    <th>Username</th>
    <th>Date</th>
    <th>Status</th>
    <th>Items</th>
        <th>Address</th>

    <th>Total (RM)</th>
</tr>
</thead>
<tbody>
<?php
$order_count = 0;
if ($all_orders && $all_orders->num_rows > 0):
    while ($order = $all_orders->fetch_assoc()):
        $order_count++;
        $hidden = $order_count > 5 ? 'style="display:none;" class="extra-order-row"' : '';
?>
    <tr <?= $hidden ?>>
        <td>#<?= htmlspecialchars($order['order_id']); ?></td>
        <td><?= htmlspecialchars($order['user_id']); ?></td>
        <td><?= htmlspecialchars($order['username']); ?></td>
        <td><?= htmlspecialchars($order['order_date']); ?></td>
<td>
    <button class="btn btn-sm update-status <?= $order['status'] === 'Complete' ? 'btn-secondary' : 'btn-success'; ?>"
            data-order-id="<?= $order['order_id']; ?>"
            <?= $order['status'] === 'Complete' ? 'disabled' : ''; ?>>
        <?= htmlspecialchars($order['status']); ?>
    </button>
</td>
        <td><?= htmlspecialchars($order['product_name']); ?></td>
                <td><?= htmlspecialchars($order['address']); ?></td>

        <td>RM<?= number_format($order['price'], 2); ?></td>
    </tr>
<?php endwhile; else: ?>
    <tr><td colspan="7">No recent orders found or query failed.</td></tr>
<?php endif; ?>
</tbody>
</table>

<?php if ($order_count > 5): ?>
<div class="text-center mt-3">
    <button id="showMoreOrders" class="btn btn-primary">Show More</button>
</div>
<?php endif; ?>

</tbody>

                </table>
                
                <!-- Logs Table -->
<div class="card mt-4">
    <div class="card-header">
        <h5>User Logs</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Action</th>
                    <th>Page</th>
                    <th>Log Time</th>
                </tr>
            </thead>
            <tbody>
     <?php 
$log_count = 0;
while ($row = $logs_result->fetch_assoc()):
    $log_count++;
    $hidden_log = $log_count > 5 ? 'style="display:none;" class="extra-log-row"' : '';
?>
    <tr <?= $hidden_log ?>>
<td><?= htmlspecialchars($row['user_id'] ?? '') ?></td>
<td><?= htmlspecialchars($row['action'] ?? '') ?></td>
<td><?= htmlspecialchars($row['page'] ?? '') ?></td>
<td><?= htmlspecialchars($row['log_time'] ?? '') ?></td>

    </tr>
<?php endwhile; ?>

            </tbody>
        </table>
        <?php if ($log_count > 5): ?>
<div class="text-center mt-3">
    <button id="showMoreLogs" class="btn btn-primary">Show More Logs</button>
</div>
<?php endif; ?>

    </div>
</div>
            </div>
        </div>
    </div>
    <script>
document.getElementById('showMoreOrders')?.addEventListener('click', function () {
    document.querySelectorAll('.extra-order-row').forEach(row => row.style.display = 'table-row');
    this.style.display = 'none';
});
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.update-status').on('click', function(e) {
        e.preventDefault();

        var button = $(this);
        var orderId = button.data('order-id');

        $.post('', { mark_complete: orderId }, function(response) {
            try {
                var data = JSON.parse(response);
                if (data.success) {
                    button.text('Complete')
                          .removeClass('btn-success')
                          .addClass('btn-secondary')
                          .prop('disabled', true);
                } else {
                    alert(data.error || 'Failed to update status.');
                }
            } catch (e) {
                alert('Unexpected response from server.');
            }
        });
    });
});
</script>
<script>
document.getElementById('showMoreLogs')?.addEventListener('click', function () {
    document.querySelectorAll('.extra-log-row').forEach(row => row.style.display = 'table-row');
    this.style.display = 'none';
});
</script>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
