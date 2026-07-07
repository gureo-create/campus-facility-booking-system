<?php
session_start();
require 'database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

$time_slots = [
    "08:00-09:00", "09:00-10:00", "10:00-11:00", "11:00-12:00",
    "13:00-14:00", "14:00-15:00", "15:00-16:00", "16:00-17:00",
];

$errors = [];
$success = false;
$selected_facility = $_GET['facility_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $facility_id = $_POST['facility_id'] ?? '';
    $date        = $_POST['date'] ?? '';
    $time_slot   = $_POST['time_slot'] ?? '';
    $purpose     = trim($_POST['purpose'] ?? '');
    $selected_facility = $facility_id;

    if (!ctype_digit($facility_id)) {
        $errors[] = "Please select a facility.";
    }

    $today = date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < $today) {
        $errors[] = "Date must be today or a future date.";
    }

    if (!in_array($time_slot, $time_slots, true)) {
        $errors[] = "Please select a valid time slot.";
    }

    if ($purpose === '') {
        $errors[] = "Purpose is required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "SELECT COUNT(*) FROM bookings
             WHERE facility_id = ? AND booking_date = ? AND time_slot = ?
             AND status IN ('pending', 'approved')"
        );
        $stmt->bind_param("iss", $facility_id, $date, $time_slot);
        $stmt->execute();
        $stmt->bind_result($conflict_count);
        $stmt->fetch();
        $stmt->close();

        if ($conflict_count > 0) {
            $errors[] = "This facility is already booked for the selected date and time slot.";
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO bookings (student_id, facility_id, booking_date, time_slot, purpose, status)
             VALUES (?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->bind_param("iisss", $student_id, $facility_id, $date, $time_slot, $purpose);
        $stmt->execute();
        $stmt->close();
        $success = true;
        $selected_facility = '';
    }
}

$facilities = $conn->query("SELECT id, name FROM facilities WHERE status = 'Available' ORDER BY name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Book a Facility</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="navbar">
        <a href="facilities.php" class="navbar-brand">Campus Facility Booking</a>
        <div class="navbar-links">
            <a href="facilities.php">Facilities</a>
            <a href="my_bookings.php">My Bookings</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="form-container">
        <div class="form-card">
            <h1>Book a Facility</h1>

            <?php if ($success): ?>
                <p class="form-success">Booking submitted and is pending approval.</p>
            <?php endif; ?>

            <?php foreach ($errors as $error): ?>
                <p class="form-error"><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>

            <form method="post" action="booking_form.php">
                <div class="form-group">
                    <label for="facility_id">Facility</label>
                    <select class="form-input" name="facility_id" id="facility_id" required>
                        <option value="">-- Select --</option>
                        <?php while ($row = $facilities->fetch_assoc()): ?>
                            <option value="<?= (int)$row['id'] ?>" <?= ((string)$row['id'] === (string)$selected_facility) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date">Date</label>
                    <input class="form-input" type="date" name="date" id="date" min="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label for="time_slot">Time Slot</label>
                    <select class="form-input" name="time_slot" id="time_slot" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($time_slots as $slot): ?>
                            <option value="<?= htmlspecialchars($slot) ?>"><?= htmlspecialchars($slot) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="purpose">Purpose</label>
                    <textarea class="form-input" name="purpose" id="purpose" rows="4" required></textarea>
                </div>

                <button class="btn btn-primary btn-block" type="submit">Submit Booking</button>
            </form>
        </div>
    </div>
</body>
</html>
