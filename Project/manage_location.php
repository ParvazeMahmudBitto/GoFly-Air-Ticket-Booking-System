<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle AJAX requests for adding, updating, or deleting a location
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $city = $conn->real_escape_string(trim($_POST['city'] ?? ''));
    $country = $conn->real_escape_string(trim($_POST['country'] ?? ''));
    $airport_code = $conn->real_escape_string(trim($_POST['airport_code'] ?? ''));
    $id = $conn->real_escape_string(trim($_POST['id'] ?? null));

    if ($action === 'add' && $city && $country && $airport_code) {
        $sql = "INSERT INTO locations (city, country, airport_code) VALUES ('$city', '$country', '$airport_code')";
        if ($conn->query($sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Location added successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error adding location: ' . $conn->error]);
        }
    } elseif ($action === 'update' && $id && $city && $country && $airport_code) {
        $sql = "UPDATE locations SET city='$city', country='$country', airport_code='$airport_code' WHERE id='$id'";
        if ($conn->query($sql)) {
            echo json_encode(['status' => 'success', 'message' => 'Location updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating location: ' . $conn->error]);
        }
    }
    exit;
}

if (isset($_GET['delete'])) {
    $id = $conn->real_escape_string($_GET['delete']);
    if ($conn->query("DELETE FROM locations WHERE id='$id'")) {
        echo json_encode(['status' => 'success', 'message' => 'Location deleted successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting location: ' . $conn->error]);
    }
    exit;
}

// Fetch all locations for initial page load
$locations = $conn->query("SELECT * FROM locations ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Locations</title>
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
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .form-header .btn {
            white-space: nowrap;
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
            width: 100%;
            max-width: 400px;
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
        
        #locationForm {
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
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #c0d1e1;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
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
            .form-header { flex-direction: column; align-items: stretch; gap: 1rem; }
            .search-bar { max-width: none; }
            .btn { width: 100%; }
        }
    </style>
</head>
<body>

<div class="card">
    <div id="message-box" style="display:none;" class="message"></div>
    <h2><i class="fas fa-map-marker-alt"></i> Manage Locations</h2>
    
    <div class="form-header">
        <button class="btn" onclick="openModal()">
            <i class="fas fa-plus-circle"></i> Add Location
        </button>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="🔍 Search by city, country or code...">
        </div>
    </div>
    
    <div class="table-container">
        <table id="locationsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>City</th>
                    <th>Country</th>
                    <th>Airport Code</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($locations && $locations->num_rows): ?>
                    <?php while($row = $locations->fetch_assoc()): ?>
                        <tr data-id="<?= $row['id'] ?>" data-city="<?= htmlspecialchars($row['city']) ?>" data-country="<?= htmlspecialchars($row['country']) ?>" data-code="<?= htmlspecialchars($row['airport_code']) ?>">
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['city']) ?></td>
                            <td><?= htmlspecialchars($row['country']) ?></td>
                            <td><?= htmlspecialchars($row['airport_code']) ?></td>
                            <td class="action-links">
                                <a href="#" onclick="editLocation(this)" title="Edit Location"><i class="fas fa-edit"></i></a>
                                <a href="#" onclick="showDeleteConfirm(<?= $row['id'] ?>)" class="delete-link" title="Delete Location"><i class="fas fa-trash-alt"></i></a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No locations found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div> <!-- End of Card -->

<!-- Location Modal for Add/Edit -->
<div id="locationModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <h3 id="modalTitle">Add Location</h3>
        <form id="locationForm">
            <input type="hidden" name="id" id="locationId">
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" name="city" id="city" required>
            </div>
            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" name="country" id="country" required>
            </div>
            <div class="form-group">
                <label for="airport_code">Airport Code</label>
                <input type="text" name="airport_code" id="airport_code" required>
            </div>
            <div class="button-group">
                <button type="submit" class="btn"><i class="fas fa-save"></i> Save Location</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal for Delete -->
<div id="confirmModal" class="modal">
    <div class="modal-content">
        <h3>Confirm Deletion</h3>
        <p>Are you sure you want to delete this location? This action cannot be undone.</p>
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
    msgBox.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    msgBox.className = 'message ' + type;
    msgBox.style.display = 'flex';
    setTimeout(() => {
        msgBox.style.display = 'none';
    }, 5000);
}

// Modal functions
function openModal(isEdit = false) {
    document.getElementById('locationModal').style.display = 'flex';
    if (!isEdit) {
        document.getElementById('modalTitle').innerText = 'Add Location';
        document.getElementById('locationForm').reset();
        document.getElementById('locationId').value = '';
    }
}
function closeModal() {
    document.getElementById('locationModal').style.display = 'none';
}

function showDeleteConfirm(id) {
    document.getElementById('confirmModal').style.display = 'flex';
    document.getElementById('confirmDeleteBtn').onclick = () => deleteLocation(id);
}
function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
}

function editLocation(el) {
    const row = el.closest('tr');
    openModal(true);
    document.getElementById('modalTitle').innerText = 'Edit Location';
    document.getElementById('locationId').value = row.dataset.id;
    document.getElementById('city').value = row.dataset.city;
    document.getElementById('country').value = row.dataset.country;
    document.getElementById('airport_code').value = row.dataset.code;
}

document.getElementById('locationForm').onsubmit = function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = formData.get('id');
    const action = id ? 'update' : 'add';
    formData.append('action', action);
    
    fetch('manage_locations.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                showMessage(res.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage(res.message, 'error');
            }
            closeModal();
        })
        .catch(err => {
            showMessage('An unexpected error occurred.', 'error');
            closeModal();
        });
};

function deleteLocation(id) {
    fetch(`manage_locations.php?delete=${id}`)
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                showMessage(res.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage(res.message, 'error');
            }
            closeConfirmModal();
        })
        .catch(err => {
            showMessage('An unexpected error occurred.', 'error');
            closeConfirmModal();
        });
}

// Live search
document.getElementById('searchInput').addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll('#locationsTable tbody tr').forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>
</body>
</html>
<?php $conn->close(); ?>
