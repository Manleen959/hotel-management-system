<?php
include 'db.php';
$tables = $conn->query("SHOW TABLES");
while ($row = $tables->fetch_array()) {
    $table = $row[0];
    echo "Table: $table\n";
    $cols = $conn->query("DESCRIBE $table");
    while ($col = $cols->fetch_assoc()) {
        print_r($col);
    }
    echo "\n---\n";
}
?>
