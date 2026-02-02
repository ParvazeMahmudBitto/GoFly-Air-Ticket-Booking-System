<?php
// Start session
session_start();

// Database configuration
$host = "localhost";         // or your DB host
$dbname = "project";   // your database name
$username = "root";          // your DB username
$password = "";              // your DB password

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submission
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize inputs
    $full_name = trim($_POST["full_name"]);
    $user_name = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Input validation
    if (empty($full_name) || empty($user_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!preg_match('/^[a-zA-Z0-9]{4,20}$/', $user_name)) {
        $error = "Username must be 4-20 characters, letters and numbers only.";
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
        $stmt->execute([
            ':username' => $user_name,
            ':email' => $email
        ]);
        if ($stmt->fetchColumn() > 0) {
            $error = "Username or email already exists.";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, email, password) 
                                   VALUES (:full_name, :username, :email, :password)");
            $stmt->execute([
                ':full_name' => $full_name,
                ':username' => $user_name,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);

            // Redirect or show success message
            header("Location: registerlogin.html?register=success");
            exit;
        }
    }
}
?>