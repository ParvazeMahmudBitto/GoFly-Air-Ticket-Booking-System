<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$offers = $conn->query("SELECT * FROM promotions WHERE status='Active' ORDER BY id DESC");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Discount Offers</title>
  <style>
    body { font-family: 'Poppins', sans-serif; margin: 0; padding: 2rem; background: #f4f8fb; }
    h2 { color: #004080; }
    .offer-card { background:#fff; padding:20px; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.05); width:250px; margin: 10px; }
    .offers-container { display: flex; flex-wrap: wrap; gap: 20px; }
  </style>
</head>
<body>

<h2>💸 Available Discount Offers</h2>

<div class="offers-container">
  <?php if ($offers->num_rows): ?>
    <?php while($offer = $offers->fetch_assoc()): ?>
      <div class="offer-card">
        <h3><?= htmlspecialchars($offer['promo_name']) ?></h3>
        <p>Code: <strong><?= htmlspecialchars($offer['promo_code']) ?></strong></p>
        <p>Discount: <strong><?= $offer['discount_percent'] ?>%</strong></p>
        <p>Status: <strong><?= htmlspecialchars($offer['status']) ?></strong></p>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No offers currently available.</p>
  <?php endif; ?>
</div>

</body>
</html>