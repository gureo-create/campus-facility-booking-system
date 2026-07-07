<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name     = trim($_POST['name'] ?? '');
        $type     = trim($_POST['type'] ?? '');
        $capacity = trim($_POST['capacity'] ?? '');
        $location = trim($_POST['location'] ?? '');

        if ($name === '' || $type === '') {
            $errors[] = "Name and type are required.";
        }

        if ($capacity !== '' && !ctype_digit($capacity)) {
            $errors[] = "Capacity must be a whole number.";
        }

        if (empty($errors)) {
            $capacity_value = $capacity === '' ? null : (int)$capacity;
            $location_value = $location === '' ? null : $location;

            $stmt = $conn->prepare(
                "INSERT INTO facilities (name, type, capacity, location, status)
                 VALUES (?, ?, ?, ?, 'Available')"
            );
            $stmt->bind_param("ssis", $name, $type, $capacity_value, $location_value);
            $stmt->execute();
            $stmt->close();
            $success = "Facility added.";
        }
    } elseif ($action === 'toggle') {
        $facility_id = $_POST['facility_id'] ?? '';

        if (ctype_digit($facility_id)) {
            $stmt = $conn->prepare(
                "UPDATE facilities
                 SET status = IF(status = 'Available', 'Unavailable', 'Available')
                 WHERE id = ?"
            );
            $stmt->bind_param("i", $facility_id);
            $stmt->execute();
            $stmt->close();
            $success = "Facility status updated.";
        }
    }
}

$facilities = $conn->query("SELECT id, name, type, capacity, location, status FROM facilities ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Facilities</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="dashboard_admin.php" class="navbar-brand">Campus Facility Booking - Admin</a>
        <div class="navbar-links">
            <a href="dashboard_admin.php">Bookings Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="form-container">
        <div class="form-card">
            <h2>Add Facility</h2>

            <?php if ($success !== ''): ?>
                <p class="form-success"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <?php foreach ($errors as $error): ?>
                <p class="form-error"><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>

            <form method="post" action="manage_facilities.php">
                <input type="hidden" name="action" value="add">

                <div class="form-group">
                    <label for="name">Name</label>
                    <input class="form-input" type="text" name="name" id="name" required>
                </div>

                <div class="form-group">
                    <label for="type">Type</label>
                    <input class="form-input" type="text" name="type" id="type" placeholder="Room, Court, Equipment..." required>
                </div>

                <div class="form-group">
                    <label for="capacity">Capacity (optional)</label>
                    <input class="form-input" type="number" name="capacity" id="capacity" min="1">
                </div>

                <div class="form-group">
                    <label for="location">Location (optional)</label>
                    <input class="form-input" type="text" name="location" id="location">
                </div>

                <button class="btn btn-primary btn-block" type="submit">Add Facility</button>
            </form>
        </div>
    </div>

    <h2 style="padding: 0 24px;">Existing Facilities</h2>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $facilities->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                        <td><?= $row['capacity'] !== null ? (int)$row['capacity'] : '—' ?></td>
                        <td><?= htmlspecialchars($row['location'] ?? '—') ?></td>
                        <td>
                            <span class="badge <?= $row['status'] === 'Available' ? 'badge-approved' : 'badge-rejected' ?>">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <form method="post" action="manage_facilities.php" style="display:inline;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="facility_id" value="<?= (int)$row['id'] ?>">
                                <button class="action-btn <?= $row['status'] === 'Available' ? 'action-btn-reject' : 'action-btn-approve' ?>" type="submit">
                                    Mark <?= $row['status'] === 'Available' ? 'Unavailable' : 'Available' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
