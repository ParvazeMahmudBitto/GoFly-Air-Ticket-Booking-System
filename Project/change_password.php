<?php
session_start();

// Check if user is logged in, if not redirect to login
if (!isset($_SESSION['user'])) {
    header("Location: login_user.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get the logged-in user's ID from session
$user_id = $_SESSION['user']['id'];
$message = "";

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    // Get the current password hash from database for the LOGGED-IN USER
    $result = $conn->query("SELECT password FROM users WHERE id='$user_id'");
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify the current password using password_verify()
        if (password_verify($current_password, $user['password'])) {
            // Hash the new password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update the password for the LOGGED-IN USER
            if ($conn->query("UPDATE users SET password='$new_password_hash' WHERE id='$user_id'")) {
                $message = "✅ Password Changed Successfully!";
            } else {
                $message = "❌ Failed to change password: " . $conn->error;
            }
        } else {
            $message = "❌ Current password is incorrect.";
        }
    } else {
        $message = "❌ User not found.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Change Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { 
        font-family: 'Poppins', sans-serif; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    .password-container {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        width: 100%;
        max-width: 450px;
    }
    h2 { 
        color: #2c3e50; 
        text-align: center;
        margin-bottom: 1.5rem;
        font-weight: 600;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    label { 
        display: block; 
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #2c3e50;
    }
    input { 
        width: 100%; 
        padding: 12px; 
        border-radius: 8px; 
        border: 1px solid #ddd;
        box-sizing: border-box;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }
    input:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    button { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white; 
        border: none;
        padding: 12px;
        border-radius: 8px;
        cursor: pointer;
        width: 100%;
        font-size: 1rem;
        font-weight: 500;
        transition: transform 0.2s ease;
    }
    button:hover {
        transform: translateY(-2px);
    }
    .message { 
        padding: 12px; 
        border-radius: 8px; 
        margin-bottom: 1.5rem;
        text-align: center;
        font-weight: 500;
    }
    .success { 
        background-color: #d4edda; 
        color: #155724; 
        border: 1px solid #c3e6cb;
    }
    .error { 
        background-color: #f8d7da; 
        color: #721c24; 
        border: 1px solid #f5c6cb;
    }
    .back-link {
        display: block;
        text-align: center;
        margin-top: 1.5rem;
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
    }
    .back-link:hover {
        text-decoration: underline;
    }
    .password-toggle {
        position: relative;
    }
    .password-toggle i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #7f8c8d;
    }
  </style>
</head>
<body>

  <div class="password-container">
    <h2>🔐 Change Password</h2>
    
    <?php if($message): ?>
      <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group password-toggle">
        <label for="current_password">Current Password</label>
        <input type="password" id="current_password" name="current_password" required>
        <i class="fas fa-eye" onclick="togglePassword('current_password')"></i>
      </div>

      <div class="form-group password-toggle">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required>
        <i class="fas fa-eye" onclick="togglePassword('new_password')"></i>
      </div>

      <button type="submit">Change Password</button>
    </form>
    
    
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
  <script>
    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = input.nextElementSibling;
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Password strength indicator (optional enhancement)
    document.getElementById('new_password').addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.getElementById('passwordStrengthBar');
        
        if (password.length > 0) {
            // Simple strength check
            let strength = 0;
            if (password.length > 7) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            console.log('Password strength:', strength);
            // You can add visual feedback here
        }
    });
  </script>
</body>
</html>