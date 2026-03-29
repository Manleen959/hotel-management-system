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

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role']; // God-Mode integration
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hotel Finder</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="auth-wrapper">

    <div class="auth-card">
        <h1>Welcome Back 👋</h1>
        <p>Log in to your account to continue</p>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid rgba(239, 68, 68, 0.2);">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['signup']) && $_GET['signup'] == 'success'): ?>
            <div style="background: rgba(34, 197, 94, 0.1); color: #22c55e; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid rgba(34, 197, 94, 0.2);">
                Account created! Please log in.
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-auth">Log In</button>
        </form>

        <div class="auth-footer">
            Don't have an account? <a href="signup.php">Create Account</a>
        </div>
    </div>

</body>
</html>