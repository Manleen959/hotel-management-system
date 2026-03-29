<?php
// Auto-detect environment: Local XAMPP vs InfinityFree Remote
if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1')) {
    $conn = new mysqli("localhost", "root", "", "hotel_management");
} else {
    // InfinityFree Credentials
    // Replace "V_PANEL_PASSWORD_HERE" with your actual InfinityFree account password!
    $conn = new mysqli("sql301.infinityfree.com", "if0_41505005", "V_PANEL_PASSWORD_HERE", "if0_41505005_hotel");
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>