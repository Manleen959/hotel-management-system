<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

include '../db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $price = $_POST['price'];
    $rating = $_POST['rating'];
    $category = $conn->real_escape_string($_POST['category']);
    $city = $conn->real_escape_string($_POST['city']);
    $amenities = $conn->real_escape_string($_POST['amenities']);
    $img = $conn->real_escape_string($_POST['img']);
    $rooms = (int)$_POST['rooms'];

    $sql = "INSERT INTO hotels (name, latitude, longitude, price, rating, total_rooms, category, city, amenities, image_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddidsssss", $name, $lat, $lng, $price, $rating, $rooms, $category, $city, $amenities, $img);

    if ($stmt->execute()) {
        $message = "Property Added Successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property - God-Mode Panel</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-nav { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .admin-nav a { padding: 0.8rem 1.5rem; background: rgba(255,255,255,0.05); border-radius: 8px; font-weight: 500; transition: 0.3s; border: 1px solid var(--glass-border); color: #fff; text-decoration: none;}
        .admin-nav a:hover, .admin-nav a.active { background: rgba(34, 211, 238, 0.1); color: var(--accent); border-color: var(--accent); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
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

    <h2 style="margin-bottom: 1.5rem; color: var(--text-color);">Add New Property</h2>

    <div class="auth-card" style="width: 100%; max-width: 800px; margin: 0; padding: 2rem;">
        
        <?php if ($message): ?>
            <div style="background: rgba(34, 197, 94, 0.1); color: #22c55e; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid rgba(34, 197, 94, 0.2);">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Property Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="form-control" required style="background: rgba(30, 41, 59, 0.9); border: 1px solid var(--glass-border); color: white;">
                        <option value="Hotel">Hotel</option>
                        <option value="Resort">Resort</option>
                        <option value="Villa">Villa</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>City / Location</label>
                    <input type="text" name="city" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Price per Night (₹)</label>
                    <input type="number" name="price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Latitude</label>
                    <input type="number" step="any" name="lat" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Longitude</label>
                    <input type="number" step="any" name="lng" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Rating (1-5)</label>
                    <input type="number" step="0.1" name="rating" min="1" max="5" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Total Rooms</label>
                    <input type="number" name="rooms" class="form-control" required>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Amenities (Comma separated: Wifi, Pool, Spa, Gym, Breakfast)</label>
                    <input type="text" name="amenities" class="form-control" placeholder="E.g. Wifi, Breakfast" required>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Image URL (Unsplash/High Res)</label>
                    <input type="url" name="img" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn-auth" style="margin-top: 1rem;"><i class="fa fa-plus"></i> Deploy Property Data</button>
        </form>
    </div>

</div>

</body>
</html>