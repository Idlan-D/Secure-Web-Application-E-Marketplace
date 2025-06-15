<?php
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Reset Password - Lan Bakery</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .card {
      max-width: 480px;
      padding: 2rem;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      border-radius: 12px;
    }
    h3 {
      margin-bottom: 1.5rem;
      font-weight: 600;
      color: #343a40;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="card">
    <h3>Reset Your Password</h3>
    <form action="update_password.php" method="post" novalidate>
      <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>" />
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>" />

      <div class="mb-3">
        <label for="new_password" class="form-label">New Password</label>
        <input
          type="password"
          id="new_password"
          name="new_password"
          class="form-control"
          placeholder="Enter new password"
          required
          minlength="8"
          autocomplete="new-password"
        />
        <div class="invalid-feedback">Password must be at least 8 characters long.</div>
      </div>

      <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input
          type="password"
          id="confirm_password"
          name="confirm_password"
          class="form-control"
          placeholder="Confirm new password"
          required
          minlength="8"
          autocomplete="new-password"
        />
        <div class="invalid-feedback">Passwords do not match.</div>
      </div>

      <button type="submit" class="btn btn-primary w-100">Reset Password</button>
      <div class="mt-3 text-center">
        <a href="login.html" class="text-decoration-none">Back to Login</a>
      </div>
    </form>
  </div>

  <script>
    (() => {
      'use strict';
      const form = document.querySelector('form');
      const newPassword = form.querySelector('#new_password');
      const confirmPassword = form.querySelector('#confirm_password');

      form.addEventListener('submit', e => {
        if (!form.checkValidity() || newPassword.value !== confirmPassword.value) {
          e.preventDefault();
          e.stopPropagation();
          if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Passwords do not match.");
          } else {
            confirmPassword.setCustomValidity("");
          }
        } else {
          confirmPassword.setCustomValidity("");
        }
        form.classList.add('was-validated');
      }, false);
    })();
  </script>
</body>
</html>
