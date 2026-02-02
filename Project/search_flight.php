<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$origin = $conn->real_escape_string($_GET['origin'] ?? '');
$destination = $conn->real_escape_string($_GET['destination'] ?? '');
$date = $conn->real_escape_string($_GET['date'] ?? '');

$query = "SELECT flights.*, airlines.name AS airline_name FROM flights 
          JOIN airlines ON flights.airline_id = airlines.id
          WHERE origin LIKE '%$origin%' AND destination LIKE '%$destination%'";

if ($date) {
    $query .= " AND DATE(departure_time) = '$date'";
}

$query .= " ORDER BY departure_time ASC";

$result = $conn->query($query);

$flights = [];
while ($row = $result->fetch_assoc()) {
    $flights[] = $row;
}

header('Content-Type: application/json');
echo json_encode($flights);

$conn->close();