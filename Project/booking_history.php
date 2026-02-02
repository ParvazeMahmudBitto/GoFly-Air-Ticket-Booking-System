<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = "";

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $conn->real_escape_string($_POST['id']);
    $seat_no = $conn->real_escape_string($_POST['seat_no']);
    $status = $conn->real_escape_string($_POST['status']);
    if ($conn->query("UPDATE bookings SET seat_no='$seat_no', status='$status' WHERE id='$id'")) {
        $message = "✅ Booking Updated!";
    } else {
        $message = "❌ Failed to update booking.";
    }
}

// Handle cancel
if (isset($_GET['cancel'])) {
    $id = $conn->real_escape_string($_GET['cancel']);
    if ($conn->query("UPDATE bookings SET status='Cancelled' WHERE id='$id'")) {
        $message = "✅ Booking Cancelled!";
    } else {
        $message = "❌ Failed to cancel booking.";
    }
}

// Load bookings
$bookings = $conn->query("SELECT bookings.*, flights.flight_no, flights.origin, flights.destination FROM bookings JOIN flights ON bookings.flight_id = flights.id ORDER BY booking_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Booking History</title>
  <style>
    body { font-family: 'Poppins', sans-serif; margin: 0; padding: 2rem; background: #f4f8fb; }
    h2 { color: #004080; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 12px 15px; border: 1px solid #ddd; }
    th { background-color: #004080; color: white; }
    tr:hover { background-color: #f1f6fb; }
    .message { margin-top: 10px; font-weight: 600; color: green; }
    input, select { padding: 5px; width: 80px; }
    a { margin-left: 10px; }
  </style>
</head>
<body>

<h2>📄 Booking History</h2>

<?php if($message): ?>
  <div class="message"><?= $message ?></div>
<?php endif; ?>

<table>
  <thead>
    <tr><th>ID</th><th>Flight</th><th>Passenger</th><th>Seat</th><th>Status</th><th>Actions</th></tr>
  </thead>
  <tbody>
    <?php while($b = $bookings->fetch_assoc()): ?>
    <tr>
      <form method="POST" action="">
        <td><?= $b['id'] ?><input type="hidden" name="id" value="<?= $b['id'] ?>"></td>
        <td><?= $b['flight_no'] ?> (<?= $b['origin'] ?> - <?= $b['destination'] ?>)</td>
        <td><?= $b['passenger_name'] ?></td>
        <td><input type="text" name="seat_no" value="<?= $b['seat_no'] ?>"></td>
        <td>
          <select name="status">
            <option <?= $b['status']=='Confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option <?= $b['status']=='Pending' ? 'selected' : '' ?>>Pending</option>
            <option <?= $b['status']=='Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            <option <?= $b['status']=='Paid' ? 'selected' : '' ?>>Paid</option>
          </select>
        </td>
        <td>
          <button type="submit" name="update">Save</button>
          <a href="?cancel=<?= $b['id'] ?>" onclick="return confirm('Are you sure to cancel?')">Cancel</a>
          <a href="download_ticket.php?id=<?= $b['id'] ?>" target="_blank">Download Ticket</a>
        </td>
      </form>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

</body>
</html>

<?php $conn->close(); ?>