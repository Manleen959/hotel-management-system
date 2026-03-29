<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'cancel_booking') {
        // User cancelling their own booking
        $booking_id = (int)$_POST['booking_id'];
        $user_id = $_SESSION['user_id'];
        
        $sql = "UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $booking_id, $user_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo "Booking cancelled successfully.";
        } else {
            echo "Failed to cancel booking. It may already be confirmed or cancelled.";
        }
    } 
    else if ($action === 'update_booking') {
        // Admin updating booking status
        if ($_SESSION['role'] !== 'admin') {
            echo "Unauthorized admin action.";
            exit();
        }
        
        $booking_id = (int)$_POST['booking_id'];
        $status = $_POST['status']; // confirmed, cancelled
        
        $sql = "UPDATE bookings SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $booking_id);
        
        if ($stmt->execute()) {
            echo "Booking marked as " . ucfirst($status) . ".";
        } else {
            echo "Failed to update booking status.";
        }
    }
    else {
        echo "Unknown action.";
    }
} else {
    echo "Invalid request.";
}
?>
