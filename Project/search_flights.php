<?php
session_start();
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$flights = [];
$message = "";

// 1. HANDLE FLIGHT SEARCH
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $origin = $conn->real_escape_string($_GET['origin']);
    $destination = $conn->real_escape_string($_GET['destination']);
    $date = $conn->real_escape_string($_GET['date']);

    // FIXED: Removed price_accessory from query since it doesn't exist
    $query = "SELECT flights.*, airlines.name AS airline_name FROM flights 
              JOIN airlines ON flights.airline_id = airlines.id
              WHERE origin LIKE '%$origin%' AND destination LIKE '%$destination%'";

    if ($date) {
        $query .= " AND DATE(departure_time) = '$date'";
    }
    $query .= " ORDER BY departure_time ASC";

    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $flights[] = $row;
    }
}

// 2. HANDLE TICKET BOOKING
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_flight'])) {
    $flight_id = $conn->real_escape_string($_POST['flight_id']);
    $passenger_name = $conn->real_escape_string($_POST['passenger_name']);
    $passenger_email = $conn->real_escape_string($_POST['passenger_email']);
    $passenger_count = (int)$_POST['passenger_count'];
    $seat_preference = $conn->real_escape_string($_POST['seat_preference']);

    // FIXED: Removed price_accessory reference
    $flight_result = $conn->query("SELECT price, economy, price_business FROM flights WHERE id = $flight_id");
    
    if ($flight_result && $flight_result->num_rows > 0) {
        $flight_data = $flight_result->fetch_assoc();
        
        // Set default prices if they don't exist
        $economy_price = isset($flight_data['price_economy']) ? $flight_data['price_economy'] : 100.00;
        $business_price = isset($flight_data['price_business']) ? $flight_data['price_business'] : 300.00;
        
        $base_price = ($seat_preference == 'business') ? $business_price : $economy_price;
        $total_price = $base_price * $passenger_count;

        // Insert the booking into the database
        $sql = "INSERT INTO bookings (flight_id, passenger_name, passenger_email, passenger_count, seat_preference, total_price, status) 
                VALUES ('$flight_id', '$passenger_name', '$passenger_email', '$passenger_count', '$seat_preference', '$total_price', 'Confirmed')";

        if ($conn->query($sql)) {
            $message = "✅ Ticket booked successfully! Booking ID: " . $conn->insert_id;
            // Clear the search results after successful booking
            $flights = [];
        } else {
            $message = "❌ Error booking ticket: " . $conn->error;
        }
    } else {
        $message = "❌ Flight not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search & Book Flights</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background: #f4f8fb; padding: 20px; }
    .card { background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px; }
    .flight-item { border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #fff; }
    .btn-book { background-color: #004080; color: white; border: none; padding: 8px 16px; border-radius: 6px; }
    .btn-book:hover { background-color: #003060; }
    .message { padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    .success { background-color: #d4edda; color: #155724; }
    .error { background-color: #f8d7da; color: #721c24; }
    .price { font-weight: bold; color: #004080; }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <h2>🔍 Search & Book Flights</h2>
      
      <?php if ($message): ?>
        <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>"><?php echo $message; ?></div>
      <?php endif; ?>

      <!-- Search Form -->
      <form method="GET" action="">
        <div class="row g-3">
          <div class="col-md-3">
            <input type="text" name="origin" class="form-control" placeholder="From (e.g., Dhaka)" value="<?= htmlspecialchars($_GET['origin'] ?? '') ?>" required>
          </div>
          <div class="col-md-3">
            <input type="text" name="destination" class="form-control" placeholder="To (e.g., Dubai)" value="<?= htmlspecialchars($_GET['destination'] ?? '') ?>" required>
          </div>
          <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">
          </div>
          <div class="col-md-3">
            <button type="submit" name="search" class="btn btn-primary w-100">Search Flights</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Flight Results -->
    <?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])): ?>
      <div class="card">
        <h3>Available Flights</h3>
        
        <?php if (count($flights) > 0): ?>
          <?php foreach($flights as $flight): ?>
            <div class="flight-item">
              <div class="row">
                <div class="col-md-8">
                  <h5><?= htmlspecialchars($flight['airline_name']) ?> - <?= htmlspecialchars($flight['flight_no']) ?></h5>
                  <p>
                    <strong>From:</strong> <?= htmlspecialchars($flight['origin']) ?> |
                    <strong>To:</strong> <?= htmlspecialchars($flight['destination']) ?> |
                    <strong>Departure:</strong> <?= date('M d, Y H:i', strtotime($flight['departure_time'])) ?> |
                    <strong>Arrival:</strong> <?= date('M d, Y H:i', strtotime($flight['arrival_time'])) ?>
                  </p>
                  <p>
                    <strong>Economy Class:</strong> <span class="price">$<?= number_format(isset($flight['price_economy']) ? $flight['price_economy'] : 100.00, 2) ?></span> |
                    <strong>Business Class:</strong> <span class="price">$<?= number_format(isset($flight['price_business']) ? $flight['price_business'] : 300.00, 2) ?></span>
                  </p>
                </div>
                <div class="col-md-4 text-end">
                  
                </div>
              </div>
            </div>

            <!-- Booking Modal for each flight -->
            <div class="modal fade" id="bookModal<?= $flight['id'] ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Book Flight: <?= htmlspecialchars($flight['flight_no']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <form method="POST" action="">
                    <div class="modal-body">
                      <input type="hidden" name="flight_id" value="<?= $flight['id'] ?>">
                      
                      <div class="mb-3">
                        <label class="form-label">Passenger Name</label>
                        <input type="text" name="passenger_name" class="form-control" required>
                      </div>
                      
                      <div class="mb-3">
                        <label class="form-label">Passenger Email</label>
                        <input type="email" name="passenger_email" class="form-control" required>
                      </div>
                      
                      <div class="mb-3">
                        <label class="form-label">Number of Passengers</label>
                        <input type="number" name="passenger_count" class="form-control" min="1" max="10" value="1" required>
                      </div>
                      
                      <div class="mb-3">
                        <label class="form-label">Seat Class</label>
                        <select name="seat_preference" class="form-control" required>
                          <option value="economy">Economy Class ($<?= number_format(isset($flight['price_economy']) ? $flight['price_economy'] : 100.00, 2) ?>)</option>
                          <option value="business">Business Class ($<?= number_format(isset($flight['price_business']) ? $flight['price_business'] : 300.00, 2) ?>)</option>
                        </select>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" name="book_flight" class="btn btn-primary">Confirm Booking</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center">No flights found matching your criteria.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>