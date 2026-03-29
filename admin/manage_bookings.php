<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

include '../db.php';

// Fetch all bookings
$bookings = $conn->query("
    SELECT b.id, u.username as user_name, h.name as hotel_name, b.booking_date, b.status 
    FROM bookings b 
    JOIN hotels h ON b.hotel_id = h.id 
    JOIN users u ON b.user_id = u.id
    ORDER BY b.booking_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - God-Mode Panel</title>
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
        <a href="manage_hotels.php"><i class="fa fa-hotel"></i> Manage Hotels</a>
        <a href="manage_bookings.php" class="active"><i class="fa fa-list"></i> All Bookings</a>
    </div>

    <h2 style="margin-bottom: 1rem; color: var(--text-color);">All Global Bookings</h2>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Guest</th>
                <th>Property</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($bookings && $bookings->num_rows > 0): ?>
                <?php while ($row = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td style="color: var(--text-muted);">#<?= $row['id'] ?></td>
                        <td><strong><i class="fa fa-user-circle"></i> <?= htmlspecialchars($row['user_name']) ?></strong></td>
                        <td><?= htmlspecialchars($row['hotel_name']) ?></td>
                        <td><?= date('F j, Y', strtotime($row['booking_date'])) ?></td>
                        <td>
                            <?php 
                                $statusClass = '';
                                if ($row['status'] == 'confirmed') $statusClass = 'success';
                                else if ($row['status'] == 'pending') $statusClass = 'warning';
                                else if ($row['status'] == 'cancelled') $statusClass = 'danger';
                            ?>
                            <span class="status-badge <?= $statusClass ?>"><?= ucfirst($row['status']) ?></span>
                        </td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <button onclick="updateBooking(<?= $row['id'] ?>, 'confirmed')" class="btn-small btn-primary">Confirm</button>
                                <button onclick="updateBooking(<?= $row['id'] ?>, 'cancelled')" class="btn-small btn-danger">Reject</button>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.85rem;">No action needed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center; padding: 2rem;">No bookings found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function updateBooking(id, status) {
    if(!confirm(`Are you sure you want to mark this booking as ${status}?`)) return;
    fetch('../api/admin_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update_booking&booking_id=${id}&status=${status}`
    }).then(r => r.text()).then(res => {
        alert(res);
        location.reload();
    });
}
</script>

</body>
</html>
