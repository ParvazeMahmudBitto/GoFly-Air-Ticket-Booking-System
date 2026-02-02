<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// AJAX add booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'add') {
    $flight_id = $conn->real_escape_string($_POST['flight_id']);
    $passenger_name = $conn->real_escape_string($_POST['passenger_name']);
    $passenger_email = $conn->real_escape_string($_POST['passenger_email']);
    $seat_no = $conn->real_escape_string($_POST['seat_no']);
    $status = $conn->real_escape_string($_POST['status']);
    if ($conn->query("INSERT INTO bookings (flight_id, passenger_name, passenger_email, seat_no, status) VALUES ('$flight_id', '$passenger_name', '$passenger_email', '$seat_no', '$status')")) {
        echo 'success';
    } else echo 'error';
    exit;
}

// AJAX update booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'update') {
    $id = $conn->real_escape_string($_POST['id']);
    $flight_id = $conn->real_escape_string($_POST['flight_id']);
    $passenger_name = $conn->real_escape_string($_POST['passenger_name']);
    $passenger_email = $conn->real_escape_string($_POST['passenger_email']);
    $seat_no = $conn->real_escape_string($_POST['seat_no']);
    $status = $conn->real_escape_string($_POST['status']);
    if ($conn->query("UPDATE bookings SET flight_id='$flight_id', passenger_name='$passenger_name', passenger_email='$passenger_email', seat_no='$seat_no', status='$status' WHERE id='$id'")) {
        echo 'success';
    } else echo 'error';
    exit;
}

// AJAX delete booking
if (isset($_GET['delete'])) {
    $id = $conn->real_escape_string($_GET['delete']);
    $conn->query("DELETE FROM bookings WHERE id='$id'") ? print 'success' : print 'error';
    exit;
}

// Fetch bookings & flights for UI
$bookings = $conn->query("SELECT bookings.*, airlines.name AS airline_name, flights.flight_no FROM bookings
                         JOIN flights ON bookings.flight_id = flights.id
                         JOIN airlines ON flights.airline_id = airlines.id
                         ORDER BY bookings.booking_date DESC");

$flights_result = $conn->query("SELECT flights.id, airlines.name AS airline_name, flight_no FROM flights
                               JOIN airlines ON flights.airline_id = airlines.id
                               ORDER BY departure_time DESC");

$flights_map = [];
while ($row = $flights_result->fetch_assoc()) {
    $flights_map[$row['id']] = $row['airline_name'] . " - " . $row['flight_no'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bookings</title>
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
            width: 100%;
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .card {
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

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .btn {
            padding: 12px 25px;
            background-color: var(--accent-green);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn:hover {
            background-color: #318259;
            transform: translateY(-2px);
        }

        .search-bar {
            margin-bottom: 25px;
        }

        .search-bar input {
            width: 100%;
            padding: 12px;
            border: 1px solid #c0d1e1;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(26, 68, 111, 0.2);
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
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
        
        .action-links a {
            color: var(--secondary-blue);
            margin-right: 15px;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        
        .action-links a:hover {
            color: var(--accent-red);
        }
        
        .action-links a.delete-link {
            color: var(--accent-red);
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: var(--border-radius-xl);
            box-shadow: var(--card-shadow);
            width: 90%;
            max-width: 600px;
            position: relative;
            animation: slideIn 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 25px;
            color: var(--text-dark);
            font-size: 2rem;
            font-weight: bold;
            transition: color 0.2s;
        }
        
        .close-btn:hover,
        .close-btn:focus {
            color: var(--accent-red);
            text-decoration: none;
            cursor: pointer;
        }
        
        #bookingForm {
            display: grid;
            gap: 1.5rem;
            margin-top: 1rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--secondary-blue);
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #c0d1e1;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(26, 68, 111, 0.2);
        }
        
        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 1rem;
        }
        
        .text-center { text-align: center !important; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        @media (max-width: 768px) {
            .card { padding: 1.5rem; }
            h2 { font-size: 1.5rem; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>

<div class="card">
    <div id="message-box" style="display:none;" class="message"></div>
    <h2><i class="fas fa-ticket-alt"></i> Manage Bookings</h2>
    
    <div class="form-header">
        <button class="btn" onclick="openModal()">
            <i class="fas fa-plus-circle"></i> Add Booking
        </button>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="🔍 Search by passenger, email, or flight...">
        </div>
    </div>
    
    <div class="table-container">
        <table id="bookingsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Flight</th>
                    <th>Passenger</th>
                    <th>Email</th>
                    <th>Seat</th>
                    <th>Status</th>
                    <th>Booking Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bookings && $bookings->num_rows): ?>
                    <?php while($row = $bookings->fetch_assoc()): ?>
                        <tr data-id="<?= $row['id'] ?>" data-flight_id="<?= $row['flight_id'] ?>" data-passenger_name="<?= htmlspecialchars($row['passenger_name']) ?>" data-passenger_email="<?= htmlspecialchars($row['passenger_email']) ?>" data-seat_no="<?= htmlspecialchars($row['seat_no']) ?>" data-status="<?= $row['status'] ?>">
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['airline_name'].' - '.$row['flight_no']) ?></td>
                            <td><?= htmlspecialchars($row['passenger_name']) ?></td>
                            <td><?= htmlspecialchars($row['passenger_email']) ?></td>
                            <td><?= htmlspecialchars($row['seat_no']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td><?= htmlspecialchars($row['booking_date']) ?></td>
                            <td class="action-links">
                                <a href="#" onclick="editBooking(this)" title="Edit Booking"><i class="fas fa-edit"></i></a>
                                <a href="#" onclick="showDeleteConfirm(<?= $row['id'] ?>)" class="delete-link" title="Delete Booking"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">No bookings found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div> <!-- End of Card -->

<!-- Modal -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h3 id="modalTitle">Add Booking</h3>
        <form id="bookingForm">
            <input type="hidden" name="id">
            <div class="form-group">
                <label for="flight_id">Flight</label>
                <select name="flight_id" id="flight_id" required>
                    <option value="">Select Flight</option>
                    <?php foreach ($flights_map as $id => $label): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="passenger_name">Passenger Name</label>
                <input type="text" name="passenger_name" id="passenger_name" required>
            </div>
            <div class="form-group">
                <label for="passenger_email">Passenger Email</label>
                <input type="email" name="passenger_email" id="passenger_email" required>
            </div>
            <div class="form-group">
                <label for="seat_no">Seat No</label>
                <input type="text" name="seat_no" id="seat_no">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option>Confirmed</option>
                    <option>Pending</option>
                    <option>Cancelled</option>
                </select>
            </div>
            <div class="button-group">
                <button type="submit" class="btn"><i class="fas fa-save"></i> Save Booking</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal for Delete -->
<div id="confirmModal" class="modal">
    <div class="modal-content">
        <h3>Confirm Deletion</h3>
        <p>Are you sure you want to delete this booking? This action cannot be undone.</p>
        <div class="button-group" style="justify-content: center;">
            <button class="btn" style="background-color: var(--accent-red);" id="confirmDeleteBtn">Delete</button>
            <button class="btn" onclick="closeConfirmModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
// Function to show messages
function showMessage(message, type) {
    const msgBox = document.getElementById('message-box');
    msgBox.innerText = message;
    msgBox.className = 'message ' + type;
    msgBox.style.display = 'flex';
    setTimeout(() => {
        msgBox.style.display = 'none';
    }, 5000);
}

// Modal functions
function openModal(isEdit = false) {
    document.getElementById('bookingModal').style.display = 'flex';
    if (!isEdit) {
        document.getElementById('modalTitle').innerText = 'Add Booking';
        document.getElementById('bookingForm').reset();
        document.querySelector('[name="id"]').value = '';
    }
}
function closeModal() {
    document.getElementById('bookingModal').style.display = 'none';
}

function showDeleteConfirm(id) {
    document.getElementById('confirmModal').style.display = 'flex';
    document.getElementById('confirmDeleteBtn').onclick = () => deleteBooking(id);
}
function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

function editBooking(el) {
    const row = el.closest('tr');
    openModal(true);
    document.getElementById('modalTitle').innerText = 'Edit Booking';
    document.querySelector('[name="id"]').value = row.dataset.id;
    document.querySelector('[name="flight_id"]').value = row.dataset.flight_id;
    document.querySelector('[name="passenger_name"]').value = row.dataset.passenger_name;
    document.querySelector('[name="passenger_email"]').value = row.dataset.passenger_email;
    document.querySelector('[name="seat_no"]').value = row.dataset.seat_no;
    document.querySelector('[name="status"]').value = row.dataset.status;
}

document.getElementById('bookingForm').onsubmit = function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = formData.get('id');
    fetch(`?action=${id ? 'update' : 'add'}`, { method: 'POST', body: formData })
        .then(res => res.text())
        .then(res => {
            if (res.trim() === 'success') {
                showMessage('Booking saved successfully!', 'success');
                setTimeout(() => location.reload(), 1500); // Reload after a delay for visual confirmation
            } else {
                showMessage('Failed to save booking.', 'error');
            }
            closeModal();
        });
};

function deleteBooking(id) {
    fetch(`?delete=${id}`)
        .then(res => res.text())
        .then(res => {
            if (res.trim() === 'success') {
                showMessage('Booking deleted successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage('Failed to delete booking.', 'error');
            }
            closeConfirmModal();
        });
}

// Live search
document.getElementById('searchInput').addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#bookingsTable tbody tr').forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
</body>
</html>

<?php $conn->close(); ?>
