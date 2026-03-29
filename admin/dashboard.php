<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

include '../db.php';

// Stats
$total_hotels = $conn->query("SELECT COUNT(*) as c FROM hotels")->fetch_assoc()['c'];
$total_bookings = $conn->query("SELECT COUNT(*) as c FROM bookings")->fetch_assoc()['c'];
$pending_bookings = $conn->query("SELECT COUNT(*) as c FROM bookings WHERE status='pending'")->fetch_assoc()['c'];

// Recent Bookings
$bookings = $conn->query("
    SELECT b.id, u.username as user_name, h.name as hotel_name, b.booking_date, b.status 
    FROM bookings b 
    JOIN hotels h ON b.hotel_id = h.id 
    JOIN users u ON b.user_id = u.id
    ORDER BY b.booking_date DESC LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>God-Mode Panel - Hotel Finder</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: rgba(30,41,59,0.9); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--glass-border); text-align: center; }
        .stat-card h3 { font-size: 2rem; color: var(--accent); margin-bottom: 0.5rem; }
        .stat-card p { color: var(--text-muted); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        
        .admin-nav { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .admin-nav a { padding: 0.8rem 1.5rem; background: rgba(255,255,255,0.05); border-radius: 8px; font-weight: 500; transition: 0.3s; border: 1px solid var(--glass-border); }
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
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $total_hotels ?></h3>
            <p><i class="fa fa-building"></i> Total Properties</p>
        </div>
        <div class="stat-card">
            <h3><?= $total_bookings ?></h3>
            <p><i class="fa fa-calendar-check"></i> Total Bookings</p>
        </div>
        <div class="stat-card">
            <h3 style="color: #facc15;"><?= $pending_bookings ?></h3>
            <p><i class="fa fa-clock"></i> Pending Confirmations</p>
        </div>
    </div>

    <div class="admin-nav">
        <a href="dashboard.php" class="active"><i class="fa fa-home"></i> Dashboard Overview</a>
        <a href="manage_hotels.php"><i class="fa fa-hotel"></i> Manage Hotels</a>
        <a href="manage_bookings.php"><i class="fa fa-list"></i> All Bookings</a>
    </div>

    <h2 style="margin-bottom: 1rem; color: var(--text-color);">Recent Activity</h2>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Booking ID</th>
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
                        <td>#<?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['user_name']) ?></td>
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
                                <span style="color: var(--text-muted); font-size: 0.85rem;">Resolved</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center; padding: 2rem;">No recent bookings.</td></tr>
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
