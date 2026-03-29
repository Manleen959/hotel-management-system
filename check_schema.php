<?php
include 'db.php';
$tables = ['hotels', 'bookings', 'users'];
foreach ($tables as $table) {
    echo "--- TABLE: $table ---\n";
    $cols = $conn->query("DESCRIBE $table");
    while ($col = $cols->fetch_assoc()) {
        print_r($col);
    }
}
?>
