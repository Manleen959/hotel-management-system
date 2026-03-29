<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../db.php';
$user_id = $_SESSION['user_id'];

// Fetch user bookings
$sql = "SELECT b.id, h.name as hotel_name, h.image_url, h.city, b.booking_date, b.status 
        FROM bookings b 
        JOIN hotels h ON b.hotel_id = h.id 
        WHERE b.user_id = ? 
        ORDER BY b.booking_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Premium Stays</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
    <h2>🏨 Hotel Finder</h2>
    <div class="nav-links">
        <span style="font-weight: 500;"><i class="fa fa-user-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="../index.php">Browse</a>
        <a href="my_bookings.php" style="color: var(--accent);">My Bookings</a>
        <a href="../auth/logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="admin-container">
    <h2 style="margin-bottom: 2rem; color: var(--accent);"><i class="fa fa-calendar-alt"></i> My Bookings History</h2>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Property</th>
                <th>Location</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="display: flex; align-items: center; gap: 10px;">
                            <img src="<?= htmlspecialchars($row['image_url']) ?>" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
                            <strong><?= htmlspecialchars($row['hotel_name']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($row['city']) ?></td>
                        <td><?= date('F j, Y', strtotime($row['booking_date'])) ?></td>
                        <td>
                            <?php 
                                $statusClass = '';
                                if ($row['status'] == 'confirmed') $statusClass = 'success';
                                else if ($row['status'] == 'pending') $statusClass = 'warning';
                                else if ($row['status'] == 'cancelled') $statusClass = 'danger';
                            ?>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['status'] == 'pending'): ?>
                                <button onclick="cancelBooking(<?= $row['id'] ?>)" class="btn-small btn-danger"><i class="fa fa-times"></i> Cancel</button>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 0.85rem;">No actions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" style="text-align: center; padding: 2rem;">No bookings found. <a href="../index.php" style="color: var(--accent);">Browse hotels</a></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function cancelBooking(id) {
    if(!confirm('Are you sure you want to cancel this booking?')) return;
    fetch('../api/admin_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=cancel_booking&booking_id=${id}`
    }).then(r => r.text()).then(res => {
        alert(res);
        location.reload();
    });
}
</script>

</body>
</html>
