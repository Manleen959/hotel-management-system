<?php
include '../db.php';

$sql = "SELECT * FROM hotels";
$result = $conn->query($sql);

$hotels = [];

while($row = $result->fetch_assoc()) {
    $hotels[] = $row;
}

echo json_encode($hotels);
?>