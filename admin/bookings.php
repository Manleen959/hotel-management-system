<?php include '../db.php'; ?>

<h2>All Bookings</h2>

<table border="1" cellpadding="10">
<tr>
  <th>Name</th>
  <th>Hotel ID</th>
  <th>Date</th>
  <th>Status</th>
</tr>

<?php
$result = $conn->query("SELECT * FROM bookings");

while($row = $result->fetch_assoc()){
  echo "<tr>
    <td>{$row['user_name']}</td>
    <td>{$row['hotel_id']}</td>
    <td>{$row['booking_date']}</td>
    <td>{$row['status']}</td>
  </tr>";
}
?>
</table>