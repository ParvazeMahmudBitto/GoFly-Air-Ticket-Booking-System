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
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password_raw = $_POST['password'];
    $password = password_hash($password_raw, PASSWORD_DEFAULT);

    // Check if the email is already registered to avoid duplicates
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($check->num_rows) {
        $message = "❌ Email already registered.";
    } else {
        // Insert the new user into the database
        if ($conn->query("INSERT INTO users (name, email, phone, password) VALUES ('$name', '$email', '$phone', '$password')")) {
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
    <title>User Registration</title>
    <!-- Tailwind CSS CDN for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

<div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-sm transform transition duration-500 hover:scale-105">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Create Account</h2>

    <!-- Display registration message -->
    <?php if ($message): ?>
        <div class="message text-center mb-4 p-3 rounded-lg <?php echo strpos($message, '✅') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- Registration form -->
    <form method="POST" action="" class="space-y-4">
        <div>
            <input type="text" name="name" placeholder="Full Name" required 
                   class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300 placeholder-gray-500">
        </div>
        <div>
            <input type="email" name="email" placeholder="Email" required
                   class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300 placeholder-gray-500">
        </div>
        <div>
            <input type="text" name="phone" placeholder="Phone" required
                   class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300 placeholder-gray-500">
        </div>
        <div>
            <input type="password" name="password" placeholder="Password" required
                   class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300 placeholder-gray-500">
        </div>
        <button type="submit" 
                class="w-full bg-blue-600 text-white font-semibold p-3 rounded-lg hover:bg-blue-700 transition duration-300 shadow-md">
            Register
        </button>
    </form>

    <p class="text-center text-sm mt-4 text-gray-600">
        Already have an account? 
        <a href="login_user.php" class="text-blue-600 font-semibold hover:underline transition duration-300">Login</a>
    </p>
</div>

</body>
</html>
