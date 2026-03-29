<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized. Please login.";
    exit();
}

if(isset($_POST['hotel_id'])){
    $hotel_id = (int)$_POST['hotel_id'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO bookings (user_id, hotel_id, booking_date, status)
            VALUES (?, ?, CURDATE(), 'pending')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $hotel_id);

    if($stmt->execute()){
        echo "Booking Requested. Waiting for confirmation.";
    } else {
        echo "Booking Failed: " . $conn->error;
    }
} else {
    echo "No valid data received";
}
?>