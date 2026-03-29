<?php
$conn = new mysqli('localhost', 'root', '', 'hotel_management');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Add image_url if not exists
$check = $conn->query("SHOW COLUMNS FROM hotels LIKE 'image_url'");
if ($check->num_rows == 0) {
    $conn->query("ALTER TABLE hotels ADD COLUMN image_url VARCHAR(255) DEFAULT NULL");
}

// Populate with high-quality images
$hotels = [
    1 => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&q=80&w=1200', // Luxury Hotel
    2 => 'https://images.unsplash.com/photo-1551882547-ff43c61f32a0?auto=format&fit=crop&q=80&w=1200', // Modern Resort
    3 => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&q=80&w=1200'  // Pool Villa
];

foreach ($hotels as $id => $url) {
    $stmt = $conn->prepare("UPDATE hotels SET image_url=? WHERE id=?");
    $stmt->bind_param("si", $url, $id);
    $stmt->execute();
}

echo "Database updated successfully!";
?>
