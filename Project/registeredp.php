<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login_user.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Dashboard - Air Ticket Booking</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    body { background: linear-gradient(to right, #f0f4f8, #e2ebf0); color: #333; min-height: 100vh; display: flex; flex-direction: column; }
    header { background-color: #003366; color: white; padding: 1rem 2rem; font-size: 1.5rem;
             display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
    .logout-btn { background-color: #ff4d4d; border: none; padding: 0.5rem 1rem; color: white;
                  border-radius: 8px; cursor: pointer; font-weight: 500; text-decoration: none; }
    .container { display: flex; flex: 1; overflow: hidden; }
    aside { background-color: #004080; padding: 2rem 1rem; min-width: 250px; display: flex; flex-direction: column; gap: 1rem; }
    aside button { background: #ffffff; border: none; border-radius: 12px; padding: 0.8rem 1rem;
                   font-size: 1rem; text-align: left; cursor: pointer; transition: 0.3s; font-weight: 500; }
    aside button:hover { background: #e6f0ff; }
    iframe { flex: 1; border: none; width: 100%; height: 100vh; }
  </style>
</head>
<body>
  <header>
    <span>🎫 User Dashboard - GoFly | Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?></span>
    <button class="logout-btn" onclick="window.location.href='frontpage.html';">Logout</button>
  </header>

  <div class="container">
    <aside>
      <button onclick="loadPage('search_flights.php')">🔍 Search Flights</button>
      <button onclick="loadPage('book_tickets.php')">🎫 Book Tickets</button>
      <button onclick="loadPage('make_payment.php')">💳 Make Payments</button>
      <button onclick="loadPage('booking_history.php')">📄 Booking History</button>
      <button onclick="loadPage('update_profile.php')">👤 Update Profile</button>
      <button onclick="loadPage('change_password.php')">🔐 Change Password</button>
      <button onclick="loadPage('view_offers.php')">💸 View Offers</button>
      <button onclick="loadPage('complaints_feedback.php')">📩 Complaints / Feedback</button>
    </aside>

    <iframe id="content-frame" src="search_flights.php"></iframe>
  </div>

  <script>
    function loadPage(page) {
      document.getElementById('content-frame').src = page;
    }
  </script>
</body>
</html>