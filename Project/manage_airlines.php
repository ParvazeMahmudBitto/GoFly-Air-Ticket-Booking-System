<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success_message = $error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $code    = $conn->real_escape_string(trim($_POST['code'] ?? ''));
    $country = $conn->real_escape_string(trim($_POST['country'] ?? ''));

    if (isset($_POST['add_airline'])) {
        if ($name && $code) {
            if ($conn->query("INSERT INTO airlines (name, code, country) VALUES ('$name','$code','$country')")) {
                $success_message = "Airline added successfully!";
            } else {
                $error_message = "Error adding airline: " . $conn->error;
            }
        } else {
            $error_message = "Name and code are required.";
        }
    }

    if (isset($_POST['update_airline'])) {
        $id = $_POST['id'] ?? null;
        if ($id && $name && $code) {
            if ($conn->query("UPDATE airlines SET name='$name', code='$code', country='$country' WHERE id='$id'")) {
                $success_message = "Airline updated successfully!";
            } else {
                $error_message = "Error updating airline: " . $conn->error;
            }
        } else {
            $error_message = "ID, name, and code are required to update.";
        }
    }
}

if (isset($_GET['delete'])) {
    $id = $conn->real_escape_string($_GET['delete']);
    if ($conn->query("DELETE FROM airlines WHERE id='$id'")) {
        $success_message = "Airline deleted successfully!";
    } else {
        $error_message = "Error deleting airline: " . $conn->error;
    }
}

$edit_mode = false;
$edit_data = [];
if (isset($_GET['edit'])) {
    $id     = $conn->real_escape_string($_GET['edit']);
    $result = $conn->query("SELECT * FROM airlines WHERE id='$id'");
    if ($result && $result->num_rows) {
        $edit_mode = true;
        $edit_data = $result->fetch_assoc();
    }
}

$airlines = $conn->query("SELECT * FROM airlines");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Airlines</title>
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

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 768px) { 
            form { grid-template-columns: 1fr; } 
            h2 { font-size: 1.5rem; }
            .card { padding: 1.5rem; }
            button, a.cancel-btn { width: 100%; }
            .button-group { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="card">
        <h2><i class="fas fa-plane"></i> Manage Airlines</h2>
        <?php if ($success_message): ?>
            <div class="message success"><i class="fas fa-check-circle"></i> <?= $success_message ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="message error"><i class="fas fa-exclamation-circle"></i> <?= $error_message ?></div>
        <?php endif; ?>

        <form method="POST">
            <h3><?= $edit_mode ? '✏️ Edit Airline' : '➕ Add New Airline' ?></h3>
            <?php if ($edit_mode): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($edit_data['id']) ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="name">Airline Name</label>
                <input type="text" name="name" id="name" required value="<?= htmlspecialchars($edit_data['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="code">Airline Code</label>
                <input type="text" name="code" id="code" required value="<?= htmlspecialchars($edit_data['code'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" name="country" id="country" value="<?= htmlspecialchars($edit_data['country'] ?? '') ?>">
            </div>
            <div class="button-group full-width">
                <button type="submit" name="<?= $edit_mode ? 'update_airline' : 'add_airline' ?>">
                    <i class="fas fa-save"></i> <?= $edit_mode ? 'Update Airline' : 'Add Airline' ?>
                </button>
                <?php if ($edit_mode): ?>
                    <a href="manage_airlines.php" class="cancel-btn">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <h3><i class="fas fa-list-alt"></i> Existing Airlines</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Country</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($airlines && $airlines->num_rows): ?>
                        <?php while($row = $airlines->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['code']) ?></td>
                                <td><?= htmlspecialchars($row['country']) ?></td>
                                <td class="action-links">
                                    <a href="manage_airlines.php?edit=<?= $row['id'] ?>" title="Edit Airline"><i class="fas fa-edit"></i></a>
                                    <a href="manage_airlines.php?delete=<?= $row['id'] ?>" class="delete-link" onclick="return confirm('Are you sure you want to delete this airline?')" title="Delete Airline"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No airlines found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php $conn->close(); ?>
