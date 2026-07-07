<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? '';
    $action     = $_POST['action'] ?? '';

    $status_map = ['approve' => 'approved', 'reject' => 'rejected'];

    if (ctype_digit($booking_id) && isset($status_map[$action])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status_map[$action], $booking_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: dashboard_admin.php");
    exit;
}

$result = $conn->query(
    "SELECT bookings.id, users.name AS student_name, facilities.name AS facility_name,
            bookings.booking_date, bookings.time_slot, bookings.purpose, bookings.status
     FROM bookings
     JOIN users ON bookings.student_id = users.id
     JOIN facilities ON bookings.facility_id = facilities.id
     ORDER BY bookings.booking_date DESC, bookings.time_slot"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Bookings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="dashboard_admin.php" class="navbar-brand">Campus Facility Booking - Admin</a>
        <div class="navbar-links">
            <a href="manage_facilities.php">Manage Facilities</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <h1 style="padding: 24px 24px 0;">All Bookings</h1>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Facility</th>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $status_badges = [
                    'pending'   => 'badge-pending',
                    'approved'  => 'badge-approved',
                    'rejected'  => 'badge-rejected',
                    'cancelled' => 'badge-cancelled',
                ];
                ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                        <td><?= htmlspecialchars($row['facility_name']) ?></td>
                        <td><?= htmlspecialchars($row['booking_date']) ?></td>
                        <td><?= htmlspecialchars($row['time_slot']) ?></td>
                        <td><?= htmlspecialchars($row['purpose']) ?></td>
                        <td>
                            <span class="badge <?= $status_badges[$row['status']] ?? '' ?>">
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <form method="post" action="dashboard_admin.php" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= (int)$row['id'] ?>">
                                    <button class="action-btn action-btn-approve" type="submit" name="action" value="approve">Approve</button>
                                </form>
                                <form method="post" action="dashboard_admin.php" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= (int)$row['id'] ?>">
                                    <button class="action-btn action-btn-reject" type="submit" name="action" value="reject">Reject</button>
                                </form>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
