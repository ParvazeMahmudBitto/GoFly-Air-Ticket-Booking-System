<?php
// Start a new session or resume the existing one
session_start();
// Establish a connection to the database
$conn = new mysqli("localhost", "root", "", "project");

// Check if the database connection failed and terminate the script if so
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['full_name']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password_raw = $_POST['password'];
    $password = password_hash($password_raw, PASSWORD_DEFAULT);

    // Check if the email or username is already registered to avoid duplicates
    $check = $conn->query("SELECT * FROM users WHERE email='$email' OR username='$username'");
    if ($check->num_rows) {
        $message = "❌ Email or username already registered.";
    } else {
        // Insert the new user into the database
        // NOTE: Your database table 'users' will need 'full_name' and 'username' columns.
        if ($conn->query("INSERT INTO users (full_name, username, email, password) VALUES ('$name', '$username', '$email', '$password')")) {
            $message = "✅ Registration successful. You can now <a href='login_user.php' class='text-blue-500 hover:text-blue-700 transition'>Login</a>.";
        } else {
            $message = "❌ Registration failed.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Air Ticket Booking</title>
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        body {
            /* The vibrant gradient background */
            background: linear-gradient(to right, #1f4037, #99f2c8);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0 auto;
            padding: 30px;
        }
        .form-logo {
            text-align: center;
            margin-bottom: 25px;
        }
        .form-logo i {
            font-size: 3rem;
            color: #1f4037;
        }
        .btn-register {
            background: #1f4037;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 8px;
            font-weight: 600;
        }
        .btn-register:hover {
            background: #0d281f;
            color: white;
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 15px;
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            width: 0%;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="form-logo">
                <i class="fas fa-plane"></i>
                <h3 class="mt-2">Create Your Account</h3>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert <?php echo strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registrationForm">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                </div>
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                    <div class="form-text">Must be 4-20 characters, letters and numbers only</div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <div class="form-text">Minimum 6 characters</div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    <div id="passwordMatch" class="form-text"></div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" required>
                    <label class="form-check-label" for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                </div>
                
                <button type="submit" class="btn btn-register mt-3" id="registerButton">Register</button>
            </form>
            
            <div class="mt-3 text-center">
                <p>Already have an account? <a href="login_user.php">Login here</a></p>
            </div>
        </div>
    </div>

    <!-- The user-provided script for password validation -->
    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            let strength = 0;
            
            if (password.length > 5) strength += 1;
            if (password.length > 7) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            const width = strength * 20;
            strengthBar.style.width = width + '%';
            
            if (strength < 2) {
                strengthBar.style.backgroundColor = '#dc3545'; // Red
            } else if (strength < 4) {
                strengthBar.style.backgroundColor = '#ffc107'; // Yellow
            } else {
                strengthBar.style.backgroundColor = '#28a745'; // Green
            }
        });
        
        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchText = document.getElementById('passwordMatch');
            
            if (confirmPassword === '') {
                matchText.textContent = '';
                matchText.style.color = '';
            } else if (password === confirmPassword) {
                matchText.textContent = 'Passwords match!';
                matchText.style.color = '#28a745';
            } else {
                matchText.textContent = 'Passwords do not match';
                matchText.style.color = '#dc3545';
            }
        });
        
        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;
            
            // NOTE: Using `alert()` is not recommended for a professional UI.
            // A more elegant solution would be to use a custom modal or inline error messages.
            if (password !== confirmPassword) {
                alert('Passwords do not match!');
                e.preventDefault();
            }
            
            if (!terms) {
                alert('You must agree to the terms and conditions');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
