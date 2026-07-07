<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$facilities = $conn->query("SELECT id, name, type, capacity, location, status FROM facilities ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Facilities</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="facilities.php" class="navbar-brand">Campus Facility Booking</a>
        <div class="navbar-links">
            <a href="my_bookings.php">My Bookings</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <h1 style="padding: 24px 24px 0;">Facilities</h1>

    <div class="facility-grid">
        <?php while ($row = $facilities->fetch_assoc()): ?>
            <div class="facility-card">
                <h3 class="facility-name"><?= htmlspecialchars($row['name']) ?></h3>
                <p class="facility-meta"><?= htmlspecialchars($row['type']) ?></p>
                <p class="facility-meta">Capacity: <?= $row['capacity'] !== null ? (int)$row['capacity'] : '—' ?></p>
                <p class="facility-meta">Location: <?= htmlspecialchars($row['location'] ?? '—') ?></p>

                <?php if ($row['status'] === 'Available'): ?>
                    <span class="badge badge-approved facility-status">Available</span>
                    <a class="btn btn-primary btn-block facility-book-btn" href="booking_form.php?facility_id=<?= (int)$row['id'] ?>">Book Now</a>
                <?php else: ?>
                    <span class="badge badge-rejected facility-status">Unavailable</span>
                    <button class="btn btn-disabled btn-block facility-book-btn" disabled>Book Now</button>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
