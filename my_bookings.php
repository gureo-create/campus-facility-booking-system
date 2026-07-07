<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? '';

    if (ctype_digit($booking_id)) {
        $stmt = $conn->prepare(
            "UPDATE bookings SET status = 'cancelled'
             WHERE id = ? AND student_id = ? AND status = 'pending'"
        );
        $stmt->bind_param("ii", $booking_id, $student_id);
        $stmt->execute();
        $message = $stmt->affected_rows > 0 ? "Booking cancelled." : "Unable to cancel that booking.";
        $stmt->close();
    }
}

$stmt = $conn->prepare(
    "SELECT bookings.id, facilities.name AS facility_name, bookings.booking_date,
            bookings.time_slot, bookings.purpose, bookings.status
     FROM bookings
     JOIN facilities ON bookings.facility_id = facilities.id
     WHERE bookings.student_id = ?
     ORDER BY bookings.booking_date DESC, bookings.time_slot"
);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$status_badges = [
    'pending'   => 'badge-pending',
    'approved'  => 'badge-approved',
    'rejected'  => 'badge-rejected',
    'cancelled' => 'badge-cancelled',
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Bookings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="facilities.php" class="navbar-brand">Campus Facility Booking</a>
        <div class="navbar-links">
            <a href="facilities.php">Facilities</a>
            <a href="booking_form.php">New Booking</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <h1 style="padding: 24px 24px 0;">My Bookings</h1>

    <?php if ($message !== ''): ?>
        <p style="padding: 0 24px;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Facility</th>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
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
                                <form method="post" action="my_bookings.php" onsubmit="return confirm('Cancel this booking?');" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= (int)$row['id'] ?>">
                                    <button class="action-btn action-btn-cancel" type="submit">Cancel</button>
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
