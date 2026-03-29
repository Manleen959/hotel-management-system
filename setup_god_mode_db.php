<?php
include 'db.php';

// 1. Update Users Table (Add role)
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($check->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'user'");
    // Make first user admin, or create an admin if none exists
    $conn->query("UPDATE users SET role='admin' WHERE id = 1");
}

// Ensure an admin user exists
$admin_check = $conn->query("SELECT id FROM users WHERE username='admin'");
if ($admin_check->num_rows == 0) {
    $hashed = password_hash('admin', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password, role) VALUES ('admin', '$hashed', 'admin')");
}

// 2. Update Hotels Table
$columns = [
    'category' => "VARCHAR(100) DEFAULT 'Hotel'",
    'city' => "VARCHAR(100) DEFAULT 'Unknown'",
    'address' => "TEXT",
    'amenities' => "VARCHAR(255) DEFAULT 'Wifi'"
];

foreach ($columns as $col => $type) {
    $check = $conn->query("SHOW COLUMNS FROM hotels LIKE '$col'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE hotels ADD COLUMN $col $type");
    }
}

// 3. Update Bookings Table
$check = $conn->query("SHOW COLUMNS FROM bookings LIKE 'user_id'");
if ($check->num_rows == 0) {
    // Add user_id
    $conn->query("ALTER TABLE bookings ADD COLUMN user_id INT(11) AFTER id");
    // Populate user_id based on user_name if possible (best effort), then drop user_name
    $conn->query("UPDATE bookings b JOIN users u ON b.user_name = u.username SET b.user_id = u.id");
    // For any remaining, just set it to 1
    $conn->query("UPDATE bookings SET user_id = 1 WHERE user_id IS NULL");
    
    // Now drop user_name safely
    $conn->query("ALTER TABLE bookings DROP COLUMN user_name");
}

// Leaving room_id as is due to FK constraints.

$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE hotels"); // Clear old
$conn->query("TRUNCATE TABLE bookings"); // Clear old bookings
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

$seed_hotels = [
    ['name' => 'The Grand Azure', 'lat' => 15.4989, 'lng' => 73.8278, 'price' => 12000, 'rating' => 4.8, 'rooms' => 50, 'cat' => 'Resort', 'city' => 'Goa', 'amenities' => 'Wifi, Pool, Spa', 'img' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&q=80&w=1200'],
    ['name' => 'Himalayan Retreat', 'lat' => 32.2396, 'lng' => 77.1887, 'price' => 8500, 'rating' => 4.6, 'rooms' => 30, 'cat' => 'Villa', 'city' => 'Manali', 'amenities' => 'Wifi, Breakfast', 'img' => 'https://images.unsplash.com/photo-1518733057094-95b53143d2a7?auto=format&fit=crop&q=80&w=1200'],
    ['name' => 'Metropolis Skyline', 'lat' => 19.0760, 'lng' => 72.8777, 'price' => 15000, 'rating' => 4.9, 'rooms' => 100, 'cat' => 'Hotel', 'city' => 'Mumbai', 'amenities' => 'Wifi, Gym, Spa, Breakfast', 'img' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&q=80&w=1200'],
    ['name' => 'Lakeview Serenity', 'lat' => 24.5854, 'lng' => 73.7125, 'price' => 22000, 'rating' => 5.0, 'rooms' => 25, 'cat' => 'Resort', 'city' => 'Udaipur', 'amenities' => 'Wifi, Pool, Spa, Breakfast', 'img' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&q=80&w=1200'],
    ['name' => 'Urban Boutique', 'lat' => 12.9716, 'lng' => 77.5946, 'price' => 6000, 'rating' => 4.2, 'rooms' => 40, 'cat' => 'Hotel', 'city' => 'Bangalore', 'amenities' => 'Wifi, Gym', 'img' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200'],
];

$stmt = $conn->prepare("INSERT INTO hotels (name, latitude, longitude, price, rating, total_rooms, category, city, amenities, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($seed_hotels as $h) {
    $stmt->bind_param("sddidsssss", $h['name'], $h['lat'], $h['lng'], $h['price'], $h['rating'], $h['rooms'], $h['cat'], $h['city'], $h['amenities'], $h['img']);
    $stmt->execute();
}

echo "God-Mode Database setup complete!\n";
?>
