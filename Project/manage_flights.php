<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get airlines for dropdown
$airlines_result = $conn->query("SELECT id, name FROM airlines ORDER BY name");

// Create mapping: airline_id → airline name
$airline_map = [];
while ($row = $airlines_result->fetch_assoc()) {
    $airline_map[$row['id']] = $row['name'];
}

// Handle messages
$success_message = $error_message = "";

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $airline_id = $conn->real_escape_string($_POST['airline_id'] ?? '');
    $flight_no  = $conn->real_escape_string(trim($_POST['flight_no'] ?? ''));
    $origin     = $conn->real_escape_string(trim($_POST['origin'] ?? ''));
    $destination= $conn->real_escape_string(trim($_POST['destination'] ?? ''));
    $departure  = $conn->real_escape_string($_POST['departure'] ?? '');
    $arrival    = $conn->real_escape_string($_POST['arrival'] ?? '');

    if (isset($_POST['add_flight'])) {
        if ($airline_id && $flight_no && $origin && $destination && $departure && $arrival) {
            $sql = "INSERT INTO flights (airline_id, flight_no, origin, destination, departure_time, arrival_time)
                     VALUES ('$airline_id', '$flight_no', '$origin', '$destination', '$departure', '$arrival')";
            $conn->query($sql) ? $success_message = "Flight added successfully!" : $error_message = "Error: " . $conn->error;
        } else {
            $error_message = "All fields are required.";
        }
    }

    if (isset($_POST['update_flight'])) {
        $id = $_POST['id'] ?? null;
        if ($id && $airline_id && $flight_no && $origin && $destination && $departure && $arrival) {
            $sql = "UPDATE flights SET airline_id='$airline_id', flight_no='$flight_no', origin='$origin',
                     destination='$destination', departure_time='$departure', arrival_time='$arrival' WHERE id='$id'";
            $conn->query($sql) ? $success_message = "Flight updated successfully!" : $error_message = "Error: " . $conn->error;
        } else {
            $error_message = "All fields are required to update.";
        }
    }
}

// Delete
if (isset($_GET['delete'])) {
    $id = $conn->real_escape_string($_GET['delete']);
    $conn->query("DELETE FROM flights WHERE id='$id'") ? $success_message = "Flight deleted successfully!" : $error_message = "Error deleting flight: " . $conn->error;
}

// Edit mode
$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit'])) {
    $id     = $conn->real_escape_string($_GET['edit']);
    $result = $conn->query("SELECT * FROM flights WHERE id='$id'");
    if ($result && $result->num_rows) {
        $edit_mode = true;
        $edit_data = $result->fetch_assoc();
    }
}

// Fetch flights with airline names using JOIN
$flights = $conn->query("SELECT flights.*, airlines.name AS airline_name FROM flights 
                         INNER JOIN airlines ON flights.airline_id = airlines.id
                         ORDER BY flights.departure_time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Flights</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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

        html, body { 
            margin: 0; 
            padding: 0; 
            overflow-x: hidden; 
            width: 100%; 
            font-family: 'Inter', sans-serif; 
            background: var(--light-bg); 
            color: var(--text-dark); 
        }

        .card { 
            background: white; 
            max-width: 1000px; 
            margin: auto; 
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

        .message { 
            padding: 15px; 
            margin-bottom: 25px; 
            border-radius: var(--border-radius-xl); 
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.5s ease-out;
        }

        .success { background-color: #d1f4e0; color: #20724d; }
        .error { background-color: #fce2e2; color: #9b1c1c; }

        form { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 20px 30px; 
            margin-bottom: 40px; 
            padding: 2rem;
            background: #f8fbfd;
            border-radius: var(--border-radius-xl);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        form .form-group {
            display: flex;
            flex-direction: column;
        }

        label { 
            margin-bottom: 8px; 
            font-weight: 600; 
            color: var(--secondary-blue);
        }

        input, select {
            width: 100%; 
            padding: 12px; 
            border: 1px solid #c0d1e1; 
            border-radius: 10px; 
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(26, 68, 111, 0.2);
        }

        .button-group {
            grid-column: 1 / -1;
            display: flex;
            gap: 20px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        button {
            padding: 12px 25px; 
            background-color: var(--accent-green); 
            color: white;
            border: none; 
            border-radius: 10px; 
            font-size: 1rem; 
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        button:hover { 
            background-color: #318259;
            transform: translateY(-2px);
        }

        a.cancel-btn {
            padding: 12px 25px; 
            background-color: #e0e6ec; 
            color: var(--text-dark);
            border-radius: 10px; 
            text-decoration: none; 
            display: inline-block;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        
        a.cancel-btn:hover {
            background-color: #d1d8e0;
            transform: translateY(-2px);
        }

        .table-container {
            overflow-x: auto;
        }
        
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

        .action-links a {
            color: var(--secondary-blue); 
            text-decoration: none; 
            margin-right: 15px; 
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .action-links a:hover { 
            color: var(--accent-red);
            text-decoration: underline; 
        }

        .action-links a.delete-link {
            color: var(--accent-red);
        }

        h3 {
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 1.5rem;
            color: var(--text-dark);
            font-weight: 700;
        }
        
        /* Utility Classes */
        .full-width { grid-column: 1 / -1; }
        .text-center { text-align: center !important; }

        @media (max-width: 768px) { 
            form { grid-template-columns: 1fr; } 
            h2 { font-size: 1.5rem; }
            .card { padding: 1.5rem; }
            button, a.cancel-btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="card">
        <h2><i class="fas fa-plane-departure"></i> Manage Flights</h2>
        <?php if ($success_message): ?>
            <div class="message success"><i class="fas fa-check-circle"></i> <?= $success_message ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="message error"><i class="fas fa-exclamation-circle"></i> <?= $error_message ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($edit_data['id']) ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="airline_id">Airline</label>
                <select name="airline_id" id="airline_id" required>
                    <option value="">Select Airline</option>
                    <?php foreach ($airline_map as $id => $name): ?>
                        <option value="<?= $id ?>" <?= (isset($edit_data['airline_id']) && $edit_data['airline_id'] == $id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="flight_no">Flight Number</label>
                <input type="text" name="flight_no" id="flight_no" required value="<?= htmlspecialchars($edit_data['flight_no'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="origin">Origin</label>
                <input type="text" name="origin" id="origin" required value="<?= htmlspecialchars($edit_data['origin'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="destination">Destination</label>
                <input type="text" name="destination" id="destination" required value="<?= htmlspecialchars($edit_data['destination'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="departure">Departure Time</label>
                <input type="datetime-local" name="departure" id="departure" required
                       value="<?= isset($edit_data['departure_time']) ? date('Y-m-d\TH:i', strtotime($edit_data['departure_time'])) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="arrival">Arrival Time</label>
                <input type="datetime-local" name="arrival" id="arrival" required
                       value="<?= isset($edit_data['arrival_time']) ? date('Y-m-d\TH:i', strtotime($edit_data['arrival_time'])) : '' ?>">
            </div>

            <div class="button-group">
                <button type="submit" name="<?= $edit_mode ? 'update_flight' : 'add_flight' ?>">
                    <i class="fas fa-save"></i> <?= $edit_mode ? 'Update Flight' : 'Add Flight' ?>
                </button>
                <?php if ($edit_mode): ?>
                    <a href="manage_flights.php" class="cancel-btn">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <h3><i class="fas fa-list-alt"></i> Existing Flights</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Airline</th>
                        <th>Flight No</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($flights && $flights->num_rows): ?>
                        <?php while($row = $flights->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['airline_name']) ?></td>
                                <td><?= htmlspecialchars($row['flight_no']) ?></td>
                                <td><?= htmlspecialchars($row['origin']) ?></td>
                                <td><?= htmlspecialchars($row['destination']) ?></td>
                                <td><?= htmlspecialchars($row['departure_time']) ?></td>
                                <td><?= htmlspecialchars($row['arrival_time']) ?></td>
                                <td class="action-links">
                                    <a href="manage_flights.php?edit=<?= $row['id'] ?>" title="Edit Flight"><i class="fas fa-edit"></i></a>
                                    <a href="manage_flights.php?delete=<?= $row['id'] ?>" class="delete-link" onclick="return confirm('Are you sure you want to delete this flight?')" title="Delete Flight"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center">No flights found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
