<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = "";

// Handle admin response
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $conn->real_escape_string($_POST['id']);
    $response = $conn->real_escape_string($_POST['response']);
    if ($conn->query("UPDATE complaints_feedback SET response='$response', status='Responded' WHERE id='$id'")) {
        $message = "✅ Response sent successfully!";
    } else {
        $message = "❌ Failed to send response.";
    }
}

// Load all complaints
$inquiries = $conn->query("SELECT complaints_feedback.*, users.name, users.email 
                           FROM complaints_feedback 
                           JOIN users ON complaints_feedback.user_id = users.id 
                           ORDER BY created_at DESC");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Respond to Inquiries</title>
  <style>
    body { font-family: 'Poppins', sans-serif; margin: 0; padding: 2rem; background: #f4f8fb; }
    h2 { color: #004080; }
    textarea, button { padding: 10px; border-radius: 6px; border: 1px solid #ccc; width: 100%; margin-bottom: 10px; }
    button { background-color: #004080; color: white; cursor: pointer; }
    .message { margin-top: 10px; font-weight: 600; color: green; }
    .inquiry-card { background:#fff; padding:15px; margin-bottom:15px; border-radius:8px; box-shadow:0 1px 8px rgba(0,0,0,0.05); }
  </style>
</head>
<body>

<h2>📨 Respond to User Inquiries</h2>

<?php if($message): ?>
  <div class="message"><?= $message ?></div>
<?php endif; ?>

<?php if ($inquiries->num_rows): ?>
  <?php while($i = $inquiries->fetch_assoc()): ?>
    <div class="inquiry-card">
      <strong>User:</strong> <?= htmlspecialchars($i['name']) ?> (<?= htmlspecialchars($i['email']) ?>)<br>
      <strong>Complaint:</strong><br><?= htmlspecialchars($i['message']) ?><br><br>

      <form method="POST" action="">
        <textarea name="response" placeholder="Write admin response here..." rows="3" required><?= htmlspecialchars($i['response'] ?? '') ?></textarea>
        <input type="hidden" name="id" value="<?= $i['id'] ?>">
        <button type="submit">Send Response</button>
      </form>
      <small>Status: <?= htmlspecialchars($i['status']) ?></small>
    </div>
  <?php endwhile; ?>
<?php else: ?>
  <p>No inquiries found.</p>
<?php endif; ?>

</body>
</html>