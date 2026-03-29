<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

include '../db.php';

// Fetch all hotels
$hotels = $conn->query("SELECT * FROM hotels ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hotels - God-Mode Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-nav { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .admin-nav a { padding: 0.8rem 1.5rem; background: rgba(255,255,255,0.05); border-radius: 8px; font-weight: 500; transition: 0.3s; border: 1px solid var(--glass-border); color: #fff; text-decoration: none;}
        .admin-nav a:hover, .admin-nav a.active { background: rgba(34, 211, 238, 0.1); color: var(--accent); border-color: var(--accent); }
    </style>
</head>
<body>

<nav class="navbar">
    <h2>⚡ God-Mode Panel</h2>
    <div class="nav-links">
        <span style="font-weight: 500;"><i class="fa fa-user-shield"></i> <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="../index.php">View Site</a>
        <a href="../auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="admin-container">

    <div class="admin-nav">
        <a href="dashboard.php"><i class="fa fa-home"></i> Dashboard Overview</a>
        <a href="manage_hotels.php" class="active"><i class="fa fa-hotel"></i> Manage Hotels</a>
        <a href="manage_bookings.php"><i class="fa fa-list"></i> All Bookings</a>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2 style="color: var(--text-color);">Hotel Directory</h2>
        <a href="add_hotel.php" class="btn-book" style="text-decoration: none; padding: 0.6rem 1.2rem; display: inline-block;">+ Add New Property</a>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Property</th>
                <th>City</th>
                <th>Category</th>
                <th>Specs</th>
                <th>Price/Night</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($hotels && $hotels->num_rows > 0): ?>
                <?php while ($row = $hotels->fetch_assoc()): ?>
                    <tr>
                        <td style="display: flex; align-items: center; gap: 10px;">
                            <img src="<?= htmlspecialchars($row['image_url']) ?>" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
                            <strong><?= htmlspecialchars($row['name']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($row['city']) ?></td>
                        <td><span class="badge" style="display:inline-block; padding: 4px 8px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);"><?= htmlspecialchars($row['category']) ?></span></td>
                        <td>
                            <div style="font-size: 0.85rem; color: var(--text-muted);">
                                ⭐ <?= $row['rating'] ?> | 🚪 <?= $row['total_rooms'] ?> Rooms
                            </div>
                        </td>
                        <td style="color: var(--accent); font-weight: bold;">₹<?= $row['price'] ?></td>
                        <td>
                             <span style="color: var(--text-muted); font-size: 0.85rem;">[Edit/Delete soon]</span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center; padding: 2rem;">No hotels found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
