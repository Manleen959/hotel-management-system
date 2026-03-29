<?php
session_start();
include '../db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $check = $conn->query("SELECT id FROM users WHERE username='$username'");
        if ($check->num_rows > 0) {
            $error = "Username already exists!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";
            if ($conn->query($sql)) {
                header("Location: login.php?signup=success");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Hotel Finder</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-wrapper">

    <div class="auth-card">
        <h1>Join Us 🏢</h1>
        <p>Create an account to browse hotels</p>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid rgba(239, 68, 68, 0.2);">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-auth">Sign Up</button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Log In</a>
        </div>
    </div>

</body>
</html>
