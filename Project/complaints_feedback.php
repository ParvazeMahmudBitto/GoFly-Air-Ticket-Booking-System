<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id = 1; // Simulated logged-in user
$message = "";

// Submit complaint/feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = $conn->real_escape_string($_POST['message']);
    if ($conn->query("INSERT INTO complaints_feedback (user_id, message, status) VALUES ('$user_id', '$msg', 'Pending')")) {
        $message = "✅ Complaint submitted successfully!";
    } else {
        $message = "❌ Failed to submit complaint.";
    }
}

// Load user complaints
$complaints = $conn->query("SELECT * FROM complaints_feedback WHERE user_id='$user_id' ORDER BY created_at DESC");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Complaints / Feedback</title>
  <style>
    body { font-family: 'Poppins', sans-serif; margin: 0; padding: 2rem; background: #f4f8fb; }
    h2 { color: #004080; }
    textarea, button { padding: 10px; border-radius: 6px; border: 1px solid #ccc; width: 100%; margin-bottom: 10px; }
    button { background-color: #004080; color: white; cursor: pointer; }
    .message { margin-top: 10px; font-weight: 600; color: green; }
    .complaint-card { background:#fff; padding:15px; margin-bottom:15px; border-radius:8px; box-shadow:0 1px 8px rgba(0,0,0,0.05); }
  </style>
</head>
<body>

<h2>📝 Complaints / Feedback</h2>

<?php if($message): ?>
  <div class="message"><?= $message ?></div>
<?php endif; ?>

<form method="POST" action="">
  <textarea name="message" placeholder="Write your complaint or feedback here..." rows="4" required></textarea><br>
  <button type="submit">Submit</button>
</form>

<h3>Your Previous Complaints</h3>
<?php if ($complaints->num_rows): ?>
  <?php while($c = $complaints->fetch_assoc()): ?>
    <div class="complaint-card">
      <strong>Your Complaint:</strong><br>
      <?= htmlspecialchars($c['message']) ?><br><br>
      <strong>Admin Response:</strong><br>
      <?= $c['response'] ? htmlspecialchars($c['response']) : '<em>Awaiting response...</em>' ?><br>
      <small>Status: <?= htmlspecialchars($c['status']) ?></small>
    </div>
  <?php endwhile; ?>
<?php else: ?>
  <p>No complaints submitted yet.</p>
<?php endif; ?>

</body>
</html>