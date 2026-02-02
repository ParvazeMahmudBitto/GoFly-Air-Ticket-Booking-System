<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id = 1; // Simulated logged-in user
$message = "";

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);

    if ($conn->query("UPDATE users SET name='$name', email='$email', phone='$phone' WHERE id='$user_id'")) {
        $message = "✅ Profile Updated!";
    } else {
        $message = "❌ Failed to update profile.";
    }
}

// Load profile
$result = $conn->query("SELECT * FROM users WHERE id='$user_id'");
$profile = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Profile</title>
  <style>
    body { font-family: 'Poppins', sans-serif; margin: 0; padding: 2rem; background: #f4f8fb; }
    h2 { color: #004080; }
    input, button { padding: 10px; margin-right: 10px; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 10px; width: 300px; }
    button { background-color: #004080; color: white; cursor: pointer; }
    .message { margin-top: 10px; font-weight: 600; color: green; }
  </style>
</head>
<body>

<h2>👤 Update Profile</h2>

<?php if($message): ?>
  <div class="message"><?= $message ?></div>
<?php endif; ?>

<form method="POST" action="">
  <label>Name</label><br>
  <input type="text" name="name" value="<?= htmlspecialchars($profile['name']) ?>" required><br>

  <label>Email</label><br>
  <input type="email" name="email" value="<?= htmlspecialchars($profile['email']) ?>" required><br>

  <label>Phone</label><br>
  <input type="text" name="phone" value="<?= htmlspecialchars($profile['phone']) ?>"><br>

  <button type="submit">Update Profile</button>
</form>

</body>
</html>

<?php $conn->close(); ?>