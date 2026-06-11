<?php
session_start();

// Database connection - using your config folder
$host = 'localhost';
$db   = 'car_rental';
$user = 'root';
$pass = '';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Use prepared statement for security
$query = "
    SELECT b.*, c.car_name, c.brand, c.image, c.price_per_day, c.fuel_type, c.transmission, c.seats
    FROM bookings b 
    LEFT JOIN cars c ON b.car_id = c.id 
    WHERE b.id = ? AND b.user_id = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    die("Error: Booking not found.");
}

// Calculate days
$pickup = new DateTime($booking['pickup_date']);
$return = new DateTime($booking['return_date']);
$days = $pickup->diff($return)->days;
if ($days == 0) $days = 1;

// Image path - from user/ folder go up to car/ then into uploads/
$imageServerPath = __DIR__ . '/../uploads/car_images/' . $booking['image'];
$imageWebPath = '../uploads/car_images/' . $booking['image'];
$imageExists = file_exists($imageServerPath);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Summary #<?= $booking['id'] ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f5f5; 
            padding: 40px 20px; 
            color: #000; 
        }
        
        .back-btn {
            max-width: 650px;
            margin: 0 auto 20px;
            display: block;
        }
        
        .back-btn a {
            background: #000;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .back-btn a:hover {
            background: #333;
        }
        
        .receipt-card { 
            max-width: 650px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 10px; 
            overflow: hidden;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1); 
            border: 1px solid #e0e0e0;
        }
        
        .receipt-header {
            background: #000;
            color: #fff;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .receipt-title {
            font-size: 1.5em;
            font-weight: 700;
        }
        
        .booking-ref {
            font-size: 0.85em;
            opacity: 0.7;
            margin-top: 5px;
        }
        
        .status-badge { 
            padding: 8px 20px; 
            border-radius: 5px; 
            font-size: 0.85em; 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 1px;
        }
        
        .status-confirmed { background: #fff; color: #000; }
        .status-pending { background: #333; color: #fff; }
        .status-cancelled { background: #666; color: #fff; }
        .status-completed { background: #fff; color: #000; border: 2px solid #fff; }
        
        .car-section {
            padding: 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            gap: 25px;
            align-items: center;
        }
        
        .car-image-box {
            width: 250px;
            height: 170px;
            background: #f0f0f0;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #000;
            flex-shrink: 0;
        }
        
        .car-image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .car-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3em;
            color: #999;
            background: #e0e0e0;
        }
        
        .car-info h2 {
            font-size: 1.5em;
            margin-bottom: 8px;
        }
        
        .car-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .car-meta-item {
            background: #f5f5f5;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: 600;
            border: 1px solid #e0e0e0;
        }
        
        .details-section {
            padding: 30px;
        }
        
        .section-title {
            font-size: 1.1em;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }
        
        .info-row { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            padding: 12px 0; 
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .label { 
            color: #666;
            font-size: 0.95em;
        }
        
        .value { 
            font-weight: 700; 
            color: #000;
            font-size: 0.95em;
        }
        
        .total-box { 
            background: #000; 
            color: #fff;
            padding: 25px 30px; 
            margin: 0 30px 30px;
            border-radius: 8px; 
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }
        
        .total-row.final {
            border-top: 1px solid #333;
            margin-top: 10px;
            padding-top: 15px;
        }
        
        .total-label {
            font-size: 0.95em;
            opacity: 0.8;
        }
        
        .total-value {
            font-weight: 700;
        }
        
        .total-final {
            font-size: 1.5em;
            font-weight: 700;
        }
        
        .actions-section {
            padding: 20px 30px 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            flex: 1;
            padding: 14px;
            border-radius: 5px;
            font-size: 0.95em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            min-width: 150px;
        }
        
        .btn-print {
            background: #fff;
            color: #000;
            border: 2px solid #000;
        }
        
        .btn-print:hover {
            background: #f0f0f0;
        }
        
        .btn-back {
            background: #000;
            color: #fff;
            border: 2px solid #000;
        }
        
        .btn-back:hover {
            background: #333;
        }
        
        .btn-download {
            background: #333;
            color: #fff;
            border: 2px solid #333;
        }
        
        .btn-download:hover {
            background: #000;
        }
        
        .dashed-line {
            border-top: 2px dashed #e0e0e0;
            margin: 0 30px;
        }
        
        @media print {
            body { padding: 0; background: white; }
            .back-btn, .actions-section { display: none !important; }
            .receipt-card { box-shadow: none; border: 1px solid #000; }
        }
        
        @media (max-width: 600px) {
            .car-section { flex-direction: column; }
            .car-image-box { width: 100%; height: 200px; }
            .receipt-header { flex-direction: column; gap: 15px; text-align: center; }
            .actions-section { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="back-btn">
    <a href="index.php?page=bookings">← Back to My Bookings</a>
</div>

<div class="receipt-card">
    <div class="receipt-header">
        <div>
            <div class="receipt-title">Booking Confirmation</div>
            <div class="booking-ref">Ref: <?= $booking['booking_code'] ?? 'BK-' . str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></div>
        </div>
        <span class="status-badge status-<?= strtolower($booking['booking_status'] ?? 'pending') ?>">
            <?= htmlspecialchars($booking['booking_status'] ?? 'Pending') ?>
        </span>
    </div>
    
    <div class="car-section">
        <div class="car-image-box">
            <?php if ($imageExists): ?>
                <img src="<?= htmlspecialchars($imageWebPath) ?>" 
                     alt="<?= htmlspecialchars($booking['car_name']) ?>">
            <?php else: ?>
                <div class="car-image-placeholder">🚗</div>
            <?php endif; ?>
        </div>
        <div class="car-info">
            <h2><?= htmlspecialchars($booking['car_name']) ?></h2>
            <p style="color: #666;"><?= htmlspecialchars($booking['brand']) ?></p>
            <div class="car-meta">
                <span class="car-meta-item"><?= htmlspecialchars($booking['fuel_type']) ?></span>
                <span class="car-meta-item"><?= htmlspecialchars($booking['transmission']) ?></span>
                <span class="car-meta-item"><?= htmlspecialchars($booking['seats']) ?> Seats</span>
            </div>
        </div>
    </div>
    
    <div class="details-section">
        <div class="section-title">Booking Details</div>
        
        <div class="info-row">
            <span class="label">Booking ID</span>
            <span class="value">#<?= $booking['id'] ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Pickup Date</span>
            <span class="value"><?= date('D, M d, Y', strtotime($booking['pickup_date'])) ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Return Date</span>
            <span class="value"><?= date('D, M d, Y', strtotime($booking['return_date'])) ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Duration</span>
            <span class="value"><?= $days ?> Day<?= $days > 1 ? 's' : '' ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Booking Status</span>
            <span class="value"><?= htmlspecialchars($booking['booking_status'] ?? 'Pending') ?></span>
        </div>
        
        <div class="info-row">
            <span class="label">Booked On</span>
            <span class="value"><?= date('M d, Y h:i A', strtotime($booking['created_at'])) ?></span>
        </div>
    </div>
    
    <div class="dashed-line"></div>
    
    <div class="total-box">
        <div class="total-row">
            <span class="total-label">Daily Rate</span>
            <span class="total-value">₹<?= number_format($booking['price_per_day'], 2) ?></span>
        </div>
        <div class="total-row">
            <span class="total-label">Duration</span>
            <span class="total-value"><?= $days ?> Day<?= $days > 1 ? 's' : '' ?></span>
        </div>
        <div class="total-row">
            <span class="total-label">Subtotal</span>
            <span class="total-value">₹<?= number_format($booking['price_per_day'] * $days, 2) ?></span>
        </div>
        <div class="total-row final">
            <span class="total-label" style="font-size: 1.1em; opacity: 1; font-weight: 700;">Total Amount</span>
            <span class="total-final">₹<?= number_format($booking['total_price'], 2) ?></span>
        </div>
    </div>
    
    <div class="actions-section">
        <button onclick="window.print()" class="btn-action btn-print">
            🖨️ Print Receipt
        </button>
        <a href="index.php?page=bookings" class="btn-action btn-back">
            ← My Bookings
        </a>
    </div>
</div>

</body>
</html>