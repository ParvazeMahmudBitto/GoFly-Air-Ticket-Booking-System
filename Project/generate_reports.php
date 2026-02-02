<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get overview counters
$airlines_count = $conn->query("SELECT COUNT(*) as total FROM airlines")->fetch_assoc()['total'];
$flights_count = $conn->query("SELECT COUNT(*) as total FROM flights")->fetch_assoc()['total'];
$bookings_count = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];
$users_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

// Get flight-wise booking count
$flight_bookings = $conn->query("
    SELECT flights.flight_no, airlines.name AS airline_name, COUNT(bookings.id) AS booking_count
    FROM bookings
    JOIN flights ON bookings.flight_id = flights.id
    JOIN airlines ON flights.airline_id = airlines.id
    GROUP BY bookings.flight_id
    ORDER BY booking_count DESC
    LIMIT 10
");

// Get date-wise booking report
$date_bookings = $conn->query("
    SELECT DATE(booking_date) AS booking_day, COUNT(id) AS bookings
    FROM bookings
    GROUP BY booking_day
    ORDER BY booking_day DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Define consistent color palette and typography */
        :root {
            --primary-blue: #0A2342;
            --secondary-blue: #1A446F;
            --light-bg: #EAF0F6;
            --text-dark: #333;
            --text-light: #f8f9fa;
            --accent-green: #3D9970;
            --accent-red: #E64C3C;
            --card-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            --border-radius-xl: 16px;
        }

        /* General body and typography styling */
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Main container for the card layout */
        .card-container {
            background: white;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2.5rem;
            border-radius: var(--border-radius-xl);
            box-shadow: var(--card-shadow);
        }

        h2 {
            color: var(--primary-blue);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 2rem;
            font-weight: 700;
        }

        h3 {
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: var(--text-dark);
            font-weight: 700;
        }

        /* Dashboard grid for overview counters */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Styling for the stat cards */
        .stat-card {
            background: var(--primary-blue);
            color: var(--text-light);
            padding: 2rem;
            border-radius: var(--border-radius-xl);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: #79a8e0;
        }

        .stat-card h3 {
            margin: 0.5rem 0;
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .stat-card p {
            font-size: 1rem;
            margin: 0;
            font-weight: 500;
        }

        /* Table styling for consistent look */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            font-size: 0.95rem;
            table-layout: auto;
        }

        table thead tr {
            background-color: var(--secondary-blue);
            color: var(--text-light);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 15px 20px;
            text-align: left;
            white-space: nowrap;
        }

        table th:first-child { border-top-left-radius: 10px; }
        table th:last-child { border-top-right-radius: 10px; }

        table td {
            background-color: white;
            border-bottom: 1px solid #e1e7ed;
        }

        table tbody tr {
            transition: transform 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .card-container { padding: 1.5rem; }
            h2 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

<div class="card-container">
    <h2><i class="fas fa-chart-line"></i> Reports Dashboard</h2>

    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="icon"><i class="fas fa-plane-departure"></i></div>
            <h3><?= $airlines_count ?></h3>
            <p>Airlines</p>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-plane"></i></div>
            <h3><?= $flights_count ?></h3>
            <p>Flights</p>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-ticket-alt"></i></div>
            <h3><?= $bookings_count ?></h3>
            <p>Bookings</p>
        </div>
        <div class="stat-card">
            <div class="icon"><i class="fas fa-users"></i></div>
            <h3><?= $users_count ?></h3>
            <p>Users</p>
        </div>
    </div>

    <h3>✈️ Top 10 Flights by Bookings</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Flight No</th>
                    <th>Airline</th>
                    <th>Bookings</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($flight_bookings && $flight_bookings->num_rows): ?>
                    <?php while($row = $flight_bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['flight_no']) ?></td>
                        <td><?= htmlspecialchars($row['airline_name']) ?></td>
                        <td><?= $row['booking_count'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align:center;">No flight data available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h3>📅 Daily Booking Report (Last 10 Days)</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Bookings</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($date_bookings && $date_bookings->num_rows): ?>
                    <?php while($row = $date_bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['booking_day'] ?></td>
                        <td><?= $row['bookings'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2" style="text-align:center;">No booking data available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
<?php $conn->close(); ?>
