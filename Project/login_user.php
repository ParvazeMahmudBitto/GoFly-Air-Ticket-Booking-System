<?php
// Start a new session or resume the existing one
session_start();
// Establish a connection to the database
$conn = new mysqli("localhost", "root", "", "project");

// Check if the database connection failed and terminate the script if so
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize an empty string for error messages
$message = "";

// Check if the form was submitted using the POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize the email input to prevent SQL injection
    $email = $conn->real_escape_string($_POST['email']);
    // Get the plain text password from the form
    $password = $_POST['password'];

    // Query the database to find a user with the provided email
    $result = $conn->query("SELECT * FROM users WHERE email='$email'");

    // Check if a user was found
    if ($result->num_rows) {
        $user = $result->fetch_assoc();
        // Verify the provided password against the hashed password in the database
        if (password_verify($password, $user['password'])) {
            // If the password matches, store user data in the session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone']
            ];
            // Redirect the user to the registeredp.php page upon successful login
            header('Location: registeredp.php');
            exit;
        } else {
            // Set an error message for an invalid password
            $message = "❌ Invalid email or password.";
        }
    } else {
        // Set an error message if the email was not found
        $message = "❌ Invalid email or password.";
    }
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <!-- Load Tailwind CSS from CDN for modern styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Load Inter font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Load Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Custom styles for the body and form */
        body {
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #e2e8f0;
            padding: 1rem;
        }
        .login-form-container {
            background-color: #1e293b;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 400px;
            border: 1px solid #334155;
            transition: transform 0.3s ease-in-out;
        }
        .login-form-container:hover {
            transform: translateY(-5px);
        }
        .input-field {
            width: 100%;
            padding: 1rem;
            margin-bottom: 1.25rem;
            border-radius: 10px;
            border: 1px solid #334155;
            background-color: #0f172a;
            color: #e2e8f0;
            transition: all 0.3s ease;
        }
        .input-field:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.5);
        }
        .submit-btn {
            width: 100%;
            padding: 1rem;
            border-radius: 10px;
            background-color: #38bdf8;
            color: white;
            font-weight: 700;
            transition: background-color 0.3s ease, transform 0.3s ease;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(56, 189, 248, 0.3);
        }
        .submit-btn:hover {
            background-color: #0ea5e9;
            transform: translateY(-2px);
        }
        .message {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 600;
            text-align: center;
        }
        .error-message {
            background-color: #ef4444;
            color: #fee2e2;
        }
        .success-message {
            background-color: #22c55e;
            color: #f0fdf4;
        }
    </style>
</head>
<body class="antialiased">

<div class="login-form-container">
    <div class="text-center mb-8">
        <div class="text-5xl text-[#38bdf8] mb-2">
            <i class="fas fa-user-circle"></i>
        </div>
        <h2 class="text-3xl font-bold text-white">Welcome Back</h2>
        <p class="text-slate-400 mt-2">Log in to your account</p>
        
    </div>

    <!-- Display message if it exists -->
    <?php if($message): ?>
        <div class="message error-message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <!-- Email Input with Icon -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                <i class="fas fa-envelope text-slate-400"></i>
            </div>
            <input type="email" name="email" placeholder="Email" required class="input-field pl-12">
        </div>
        
        <!-- Password Input with Icon -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                <i class="fas fa-lock text-slate-400"></i>
            </div>
            <input type="password" name="password" placeholder="Password" required class="input-field pl-12">
        </div>

        <button type="submit" class="submit-btn">
            Login
        </button>
    </form>

    <p class="text-center text-sm text-slate-400 mt-6">
        Don't have an account? <a href="register_user.php" class="text-[#38bdf8] font-medium hover:underline">Register now</a>
    </p>
</div>

</body>
</html>
