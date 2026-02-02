<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$airlines_count = $conn->query("SELECT COUNT(*) as total FROM airlines")->fetch_assoc()['total'];
$flights_count = $conn->query("SELECT COUNT(*) as total FROM flights")->fetch_assoc()['total'];
$bookings_count = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];
$users_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GoFly Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: linear-gradient(to right, #d7e9f7, #f1f7ff); /* Light sky blue → white */
      color: #333;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    h2 {
      margin: 40px 0 20px;
      font-size: 2.5rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: #003366;
    }

    .container {
      max-width: 1200px;
      width: 90%;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 25px;
    }

    .card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      padding: 2rem;
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 15px 40px rgba(0,0,0,0.25);
    }

    .icon {
      font-size: 3.5rem;
      margin-bottom: 1rem;
      color: #0055a5;
    }

    .number {
      font-size: 3rem;
      font-weight: 700;
      margin: 10px 0;
      color: #003366;
    }

    .label {
      font-size: 1.3rem;
      font-weight: 500;
      color: #444;
    }
  </style>
</head>
<body>
  <h2>GoFly Dashboard</h2>
  <div class="container">
    <div class="card">
      <div class="icon">🛫</div>
      <div class="number" data-count="<?= $airlines_count ?>">0</div>
      <div class="label">Total Airlines</div>
    </div>
    <div class="card">
      <div class="icon">✈️</div>
      <div class="number" data-count="<?= $flights_count ?>">0</div>
      <div class="label">Total Flights</div>
    </div>
    <div class="card">
      <div class="icon">📑</div>
      <div class="number" data-count="<?= $bookings_count ?>">0</div>
      <div class="label">Total Bookings</div>
    </div>
    <div class="card">
      <div class="icon">👨‍👩‍👧‍👦</div>
      <div class="number" data-count="<?= $users_count ?>">0</div>
      <div class="label">Total Users</div>
    </div>
  </div>

  <script>
    const counters = document.querySelectorAll('.number');
    counters.forEach(counter => {
      const updateCount = () => {
        const target = +counter.getAttribute('data-count');
        const count = +counter.innerText;
        const increment = target / 80; 
        if (count < target) {
          counter.innerText = Math.ceil(count + increment);
          setTimeout(updateCount, 20);
        } else {
          counter.innerText = target;
        }
      };
      updateCount();
    });
  </script>
</body>
</html>
