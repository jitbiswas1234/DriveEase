<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'Pending'");
$pending = mysqli_fetch_assoc($result)['count'];

echo json_encode([
    'pending_bookings' => $pending
]);
?>