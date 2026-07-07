<?php
session_start();
require 'database.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name             = trim($_POST['name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $errors[] = "All fields are required.";
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($password !== '' && $password !== $confirm_password) {
        $errors[] = "Password and confirm password do not match.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "This email is already registered.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
        $stmt->bind_param("sss", $name, $email, $hashed_password);
        $stmt->execute();
        $stmt->close();
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Student Registration</h1>

            <?php if ($success): ?>
                <p class="form-success">Registration successful. You can now <a href="login.php">login</a>.</p>
            <?php else: ?>

                <?php foreach ($errors as $error): ?>
                    <p class="form-error"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>

                <form method="post" action="register.php">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input class="auth-input" type="text" name="name" id="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input class="auth-input" type="email" name="email" id="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input class="auth-input" type="password" name="password" id="password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input class="auth-input" type="password" name="confirm_password" id="confirm_password" required>
                    </div>

                    <button class="auth-button" type="submit">Register</button>
                </form>

                <p class="auth-link">Already have an account? <a href="login.php">Login here</a>.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
