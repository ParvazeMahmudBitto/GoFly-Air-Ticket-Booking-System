<?php
// Establish database connection
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle AJAX requests for CRUD operations
// Add a new promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'add') {
    $promo_name = $conn->real_escape_string($_POST['promo_name']);
    $promo_code = $conn->real_escape_string($_POST['promo_code']);
    $discount_percent = $conn->real_escape_string($_POST['discount_percent']);
    $status = $conn->real_escape_string($_POST['status']);
    $sql = "INSERT INTO promotions (promo_name, promo_code, discount_percent, status) VALUES ('$promo_name', '$promo_code', '$discount_percent', '$status')";
    if ($conn->query($sql)) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit;
}

// Update an existing promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] == 'update') {
    $id = $conn->real_escape_string($_POST['id']);
    $promo_name = $conn->real_escape_string($_POST['promo_name']);
    $promo_code = $conn->real_escape_string($_POST['promo_code']);
    $discount_percent = $conn->real_escape_string($_POST['discount_percent']);
    $status = $conn->real_escape_string($_POST['status']);
    $sql = "UPDATE promotions SET promo_name='$promo_name', promo_code='$promo_code', discount_percent='$discount_percent', status='$status' WHERE id='$id'";
    if ($conn->query($sql)) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit;
}

// Delete a promotion
if (isset($_GET['delete'])) {
    $id = $conn->real_escape_string($_GET['delete']);
    $sql = "DELETE FROM promotions WHERE id='$id'";
    $conn->query($sql) ? print 'success' : print 'error';
    exit;
}

// Fetch all promotions from the database to display in the table
$promotions = $conn->query("SELECT * FROM promotions ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Promotions</title>
    <!-- Load Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Load Inter font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Load Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Custom styles for the body and modal */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #1A202C; /* Dark background */
            color: #E2E8F0; /* Light text color */
        }
        .container {
            max-width: 1000px;
            margin: auto;
            padding: 2rem;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            justify-content: center;
            align-items: center;
            z-index: 999;
        }
    </style>
</head>
<body class="bg-[#1a202c] text-[#e2e8f0] font-inter antialiased">

    <div class="container mx-auto p-4 md:p-8">
        <!-- Header and Add Promotion Button -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <h2 class="text-3xl md:text-4xl font-extrabold text-[#3b82f6] mb-4 md:mb-0">
                <i class="fas fa-percent mr-3"></i> Manage Promotions
            </h2>
            <button class="btn bg-[#3b82f6] hover:bg-[#2563eb] text-white px-6 py-3 rounded-xl font-semibold shadow-lg transition-all duration-300 transform hover:scale-105" onclick="openModal()">
                <i class="fas fa-plus mr-2"></i> Add Promotion
            </button>
        </div>

        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative">
                <input type="text" id="searchInput" placeholder="Search by name, code, or status..." class="w-full pl-12 pr-4 py-3 rounded-xl bg-gray-800 text-white placeholder-gray-400 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-[#3b82f6] transition-all duration-300">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
        </div>

        <!-- Promotions Table -->
        <div class="overflow-x-auto rounded-xl shadow-xl">
            <table class="w-full text-left table-auto" id="promotionsTable">
                <thead class="bg-[#2d3748] text-gray-300 uppercase text-sm">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">ID</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Code</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Discount (%)</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-[#2d3748] divide-y divide-gray-700">
                    <?php while ($row = $promotions->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-700 transition-colors duration-200"
                        data-id="<?= htmlspecialchars($row['id']) ?>"
                        data-promo_name="<?= htmlspecialchars($row['promo_name']) ?>"
                        data-promo_code="<?= htmlspecialchars($row['promo_code']) ?>"
                        data-discount_percent="<?= htmlspecialchars($row['discount_percent']) ?>"
                        data-status="<?= htmlspecialchars($row['status']) ?>">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-300"><?= htmlspecialchars($row['id']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($row['promo_name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($row['promo_code']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?= htmlspecialchars($row['discount_percent']) ?>%</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-xs font-semibold px-2 py-1 rounded-full <?php echo ($row['status'] == 'Active') ? 'bg-green-600 text-white' : 'bg-red-600 text-white'; ?>">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="#" class="text-[#3b82f6] hover:text-[#2563eb] mr-4 transition-colors duration-200" onclick="editPromotion(this)">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <a href="#" class="text-red-500 hover:text-red-400 transition-colors duration-200" onclick="deletePromotion(<?= htmlspecialchars($row['id']) ?>)">
                                <i class="fas fa-trash-alt mr-1"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for adding/editing promotions -->
    <div id="promoModal" class="modal">
        <div class="bg-gray-800 p-8 rounded-2xl shadow-2xl w-full max-w-lg border border-gray-700">
            <div class="flex justify-between items-center mb-6">
                <h3 id="modalTitle" class="text-2xl font-bold text-white">Add Promotion</h3>
                <span class="text-white text-3xl cursor-pointer hover:text-gray-400 transition-colors" onclick="closeModal()">&times;</span>
            </div>
            <form id="promoForm">
                <input type="hidden" name="id">
                <div class="mb-4">
                    <label for="promo_name" class="block text-gray-300 font-medium mb-1">Promotion Name</label>
                    <input type="text" name="promo_name" id="promo_name" required class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#3b82f6]">
                </div>
                <div class="mb-4">
                    <label for="promo_code" class="block text-gray-300 font-medium mb-1">Promotion Code</label>
                    <input type="text" name="promo_code" id="promo_code" required class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#3b82f6]">
                </div>
                <div class="mb-4">
                    <label for="discount_percent" class="block text-gray-300 font-medium mb-1">Discount (%)</label>
                    <input type="number" name="discount_percent" id="discount_percent" required class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#3b82f6]">
                </div>
                <div class="mb-6">
                    <label for="status" class="block text-gray-300 font-medium mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-4 py-3 rounded-lg bg-gray-700 text-white border border-gray-600 focus:outline-none focus:ring-2 focus:ring-[#3b82f6]">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="w-full bg-[#3b82f6] hover:bg-[#2563eb] text-white px-6 py-3 rounded-xl font-semibold shadow-lg transition-all duration-300 transform hover:scale-105">
                    Save Promotion
                </button>
            </form>
        </div>
    </div>

    <!-- JavaScript for interactivity -->
    <script>
        // Function to open the modal
        function openModal(isEdit = false) {
            document.getElementById('promoModal').style.display = 'flex';
            if (!isEdit) {
                document.getElementById('modalTitle').innerText = 'Add Promotion';
                document.getElementById('promoForm').reset();
                document.querySelector('[name="id"]').value = '';
            }
        }
        
        // Function to close the modal
        function closeModal() {
            document.getElementById('promoModal').style.display = 'none';
        }

        // Function to populate the modal for editing
        function editPromotion(el) {
            const row = el.closest('tr');
            openModal(true);
            document.getElementById('modalTitle').innerText = 'Edit Promotion';
            document.querySelector('[name="id"]').value = row.dataset.id;
            document.querySelector('[name="promo_name"]').value = row.dataset.promo_name;
            document.querySelector('[name="promo_code"]').value = row.dataset.promo_code;
            document.querySelector('[name="discount_percent"]').value = row.dataset.discount_percent;
            document.querySelector('[name="status"]').value = row.dataset.status;
        }

        // Handle form submission using fetch API
        document.getElementById('promoForm').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id = formData.get('id');
            const actionUrl = `?action=${id ? 'update' : 'add'}`;
            
            // Custom confirmation message
            const messageBox = document.createElement('div');
            messageBox.innerHTML = `
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-[1000]">
                    <div class="bg-gray-800 p-6 rounded-xl shadow-2xl max-w-sm text-center border border-gray-700">
                        <p class="text-white text-lg mb-4">Are you sure you want to save this promotion?</p>
                        <div class="flex justify-center space-x-4">
                            <button id="confirm-btn" class="bg-[#3b82f6] hover:bg-[#2563eb] text-white font-semibold py-2 px-4 rounded-lg">Yes</button>
                            <button id="cancel-btn" class="bg-gray-600 hover:bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg">No</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(messageBox);
            
            // Handle confirmation
            document.getElementById('confirm-btn').onclick = () => {
                messageBox.remove();
                fetch(actionUrl, { method: 'POST', body: formData })
                    .then(res => res.text())
                    .then(res => {
                        if (res.trim() === 'success') {
                            location.reload();
                        } else {
                            showCustomAlert('Failed to save promotion.');
                        }
                    })
                    .catch(() => showCustomAlert('An error occurred. Please try again.'));
            };

            document.getElementById('cancel-btn').onclick = () => {
                messageBox.remove();
            };
        };

        // Handle delete action using fetch API
        function deletePromotion(id) {
            const messageBox = document.createElement('div');
            messageBox.innerHTML = `
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-[1000]">
                    <div class="bg-gray-800 p-6 rounded-xl shadow-2xl max-w-sm text-center border border-gray-700">
                        <p class="text-white text-lg mb-4">Are you sure you want to delete this promotion?</p>
                        <div class="flex justify-center space-x-4">
                            <button id="confirm-delete-btn" class="bg-red-600 hover:bg-red-500 text-white font-semibold py-2 px-4 rounded-lg">Yes</button>
                            <button id="cancel-delete-btn" class="bg-gray-600 hover:bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg">No</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(messageBox);

            document.getElementById('confirm-delete-btn').onclick = () => {
                messageBox.remove();
                fetch(`?delete=${id}`)
                    .then(res => res.text())
                    .then(res => {
                        if (res.trim() === 'success') {
                            location.reload();
                        } else {
                            showCustomAlert('Failed to delete promotion.');
                        }
                    })
                    .catch(() => showCustomAlert('An error occurred. Please try again.'));
            };

            document.getElementById('cancel-delete-btn').onclick = () => {
                messageBox.remove();
            };
        }

        // Custom alert/message box function for user feedback
        function showCustomAlert(message) {
            const alertBox = document.createElement('div');
            alertBox.innerHTML = `
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-[1000]">
                    <div class="bg-gray-800 p-6 rounded-xl shadow-2xl max-w-sm text-center border border-gray-700">
                        <p class="text-white text-lg mb-4">${message}</p>
                        <button id="close-alert-btn" class="bg-[#3b82f6] hover:bg-[#2563eb] text-white font-semibold py-2 px-4 rounded-lg">OK</button>
                    </div>
                </div>
            `;
            document.body.appendChild(alertBox);
            document.getElementById('close-alert-btn').onclick = () => alertBox.remove();
        }

        // Live search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            document.querySelectorAll('#promotionsTable tbody tr').forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
