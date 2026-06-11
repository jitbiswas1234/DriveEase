<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$booking_id = intval($_POST['booking_id'] ?? 0);
$status = $_POST['status'] ?? '';
$payment_status = $_POST['payment_status'] ?? null;

if (!$booking_id || !$status) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$valid_statuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

// Update booking status
$query = "UPDATE bookings SET booking_status = ?";
$params = [$status];
$types = 's';

if ($payment_status) {
    $query .= ", payment_status = ?";
    $params[] = $payment_status;
    $types .= 's';
}

$query .= " WHERE id = ?";
$params[] = $booking_id;
$types .= 'i';

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // If cancelled, make car available again
    if ($status === 'Cancelled' || $status === 'Completed') {
        $booking = mysqli_fetch_assoc(mysqli_query($conn, "SELECT car_id FROM bookings WHERE id = $booking_id"));
        if ($booking) {
            mysqli_query($conn, "UPDATE cars SET status = 'Available' WHERE id = " . $booking['car_id']);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update booking']);
}

$stmt->close();
$conn->close();
?>