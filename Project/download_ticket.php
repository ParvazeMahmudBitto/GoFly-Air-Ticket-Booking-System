<?php
$conn = new mysqli("localhost", "root", "", "project");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = $conn->real_escape_string($_GET['id'] ?? '');

$result = $conn->query("SELECT bookings.*, flights.flight_no, flights.origin, flights.destination, flights.departure_time, flights.arrival_time 
                        FROM bookings 
                        JOIN flights ON bookings.flight_id = flights.id
                        WHERE bookings.id='$id'");

if ($result && $result->num_rows) {
    $booking = $result->fetch_assoc();

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="Ticket_'.$booking['id'].'.txt"');

    echo "----- Ticket Details -----\n";
    echo "Booking ID: " . $booking['id'] . "\n";
    echo "Passenger: " . $booking['passenger_name'] . "\n";
    echo "Email: " . $booking['passenger_email'] . "\n";
    echo "Flight No: " . $booking['flight_no'] . "\n";
    echo "From: " . $booking['origin'] . "\n";
    echo "To: " . $booking['destination'] . "\n";
    echo "Departure: " . $booking['departure_time'] . "\n";
    echo "Arrival: " . $booking['arrival_time'] . "\n";
    echo "Seat No: " . $booking['seat_no'] . "\n";
    echo "Status: " . $booking['status'] . "\n";
    echo "---------------------------\n";
} else {
    echo "Invalid Booking ID";
}

$conn->close();