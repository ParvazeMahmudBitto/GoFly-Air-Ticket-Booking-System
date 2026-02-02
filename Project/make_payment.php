<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = "";

// Process payment simulation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $conn->real_escape_string($_POST['booking_id']);
    $amount = $conn->real_escape_string($_POST['amount']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);

    if ($conn->query("UPDATE bookings SET status='Paid' WHERE id='$booking_id'")) {
        $message = "✅ Payment Successful!";
    } else {
        $message = "❌ Payment Failed.";
    }
}

// Get unpaid bookings
$bookings = $conn->query("SELECT * FROM bookings WHERE status != 'Paid' ORDER BY booking_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Make Payment</title>
  <style>
    body { font-family: 'Poppins', sans-serif; margin: 0; padding: 2rem; background: #f4f8fb; }
    h2 { color: #004080; }
    select, input, button { padding: 10px; margin-right: 10px; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 10px; }
    button { background-color: #004080; color: white; cursor: pointer; }
    .message { margin-top: 10px; font-weight: 600; }
  </style>
</head>
<body>

<h2>💳 Make Payment</h2>

<?php if($message): ?>
  <div class="message"><?= $message ?></div>
<?php endif; ?>

<form method="POST" action="">
  <label>Select Booking</label><br>
  <select name="booking_id" required>
    <option value="">Select Booking</option>
    <?php while($b = $bookings->fetch_assoc()): ?>
      <option value="<?= $b['id'] ?>"><?= $b['passenger_name'] ?> (<?= $b['seat_no'] ?>)</option>
    <?php endwhile; ?>
  </select><br>

  <label>Amount</label><br>
  <input type="number" name="amount" min="1" required><br>

  <label>Payment Method</label><br>
  <select name="payment_method">
    <option>Credit Card</option>
    <option>Debit Card</option>
    <option>UPI</option>
    <option>Net Banking</option>
  </select><br>

  <button type="submit">Pay Now</button>
</form>

</body>
</html>

<?php $conn->close(); ?>