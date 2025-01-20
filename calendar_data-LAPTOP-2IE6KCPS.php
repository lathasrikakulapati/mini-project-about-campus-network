<?php
include 'db_connection.php';

$query = "SELECT * FROM placement_events";
$result = $conn->query($query);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode($events);
?>
