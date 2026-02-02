<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flight_id = $conn->real_escape_string($_POST['flight_id']);
    $passenger_name = $conn->real_escape_string($_POST['passenger_name']);
    $passenger_email = $conn->real_escape_string($_POST['passenger_email']);
    $seat_no = $conn->real_escape_string($_POST['seat_no']);
    $status = 'Confirmed';

    if ($conn->query("INSERT INTO bookings (flight_id, passenger_name, passenger_email, seat_no, status) 
                      VALUES ('$flight_id', '$passenger_name', '$passenger_email', '$seat_no', '$status')")) {
        $message = "✅ Ticket Booked Successfully!";
    } else {
        $message = "❌ Failed to book ticket.";
    }
}

// Get flights
$flights = $conn->query("SELECT flights.*, airlines.name AS airline_name FROM flights JOIN airlines ON flights.airline_id = airlines.id ORDER BY departure_time ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Tickets</title>
  <style>
    body { font-family: 'Poppins', sans-serif; margin: 0; padding: 2rem; background: #f4f8fb; }
    h2 { color: #004080; }
    select, input, button { padding: 10px; margin-right: 10px; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 10px; }
    button { background-color: #004080; color: white; cursor: pointer; }
    .message { margin-top: 10px; font-weight: 600; }
  </style>
</head>
<body>

<h2>🎫 Book Tickets</h2>

<?php if($message): ?>
  <div class="message"><?= $message ?></div>
<?php endif; ?>

<form method="POST" action="">
  <label>Choose Flight</label><br>
  <select name="flight_id" required>
    <option value="">Select Flight</option>
    <?php while($f = $flights->fetch_assoc()): ?>
      <option value="<?= $f['id'] ?>"><?= $f['flight_no'] ?> - <?= $f['origin'] ?> to <?= $f['destination'] ?> (<?= $f['airline_name'] ?>)</option>
    <?php endwhile; ?>
  </select><br>

  <label>Passenger Name</label><br>
  <input type="text" name="passenger_name" required><br>

  <label>Passenger Email</label><br>
  <input type="email" name="passenger_email" required><br>

  <label>Seat No (optional)</label><br>
  <input type="text" name="seat_no"><br>

  <button type="submit">Book Now</button>
</form>

</body>
</html>

<?php $conn->close(); ?>