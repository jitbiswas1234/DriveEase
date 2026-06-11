<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/base_url.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    if (isset($_GET['redirect'])) {
        header('Location: ' . $base_url . 'login.php?type=admin');
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    }
    exit();
}

// Get car ID
$car_id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_id = intval($_POST['id'] ?? 0);
} else {
    $car_id = intval($_GET['id'] ?? 0);
}

if (!$car_id) {
    if (isset($_GET['redirect'])) {
        header('Location: ' . $base_url . 'admin/index.php?page=cars&error=' . urlencode('Invalid car ID'));
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid car ID']);
    }
    exit();
}

// Check if car has active bookings
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE car_id = ? AND booking_status IN ('Pending', 'Confirmed')");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();
$active_bookings = $result->fetch_assoc()['count'];
$stmt->close();

if ($active_bookings > 0) {
    $message = 'Cannot delete car with active bookings';
    if (isset($_GET['redirect'])) {
        header('Location: ' . $base_url . 'admin/index.php?page=cars&error=' . urlencode($message));
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
    }
    exit();
}

// Get car image before deleting
$stmt = $conn->prepare("SELECT image FROM cars WHERE id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();
$stmt->close();

if (!$car) {
    $message = 'Car not found';
    if (isset($_GET['redirect'])) {
        header('Location: ' . $base_url . 'admin/index.php?page=cars&error=' . urlencode($message));
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
    }
    exit();
}

// Delete car
$stmt = $conn->prepare("DELETE FROM cars WHERE id = ?");
$stmt->bind_param("i", $car_id);

if ($stmt->execute()) {
    // Delete car image if exists
    if (!empty($car['image'])) {
        $image_path = '../../uploads/car_images/' . $car['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    if (isset($_GET['redirect'])) {
        header('Location: ' . $base_url . 'admin/index.php?page=cars&success=' . urlencode('Car deleted successfully'));
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Car deleted successfully']);
    }
} else {
    $message = 'Failed to delete car';
    if (isset($_GET['redirect'])) {
        header('Location: ' . $base_url . 'admin/index.php?page=cars&error=' . urlencode($message));
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
    }
}

$stmt->close();
$conn->close();
?>