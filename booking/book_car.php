<?php
session_start();
require_once("../config/database.php");
require_once("../config/base_url.php");

if (!isset($_SESSION['user_id'])) {
    header("Location:" . $base_url . "/login.php");
    exit();
}

$car_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$car_id) {
    header("Location:../cars.php");
    exit();
}

// Fetch car details
$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();

if (!$car) {
    header("Location:../cars.php");
    exit();
}

// Fetch booked dates
$stmt = $conn->prepare("SELECT pickup_date, return_date FROM bookings WHERE car_id = ? AND booking_status != 'Cancelled' ORDER BY pickup_date ASC");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$booked_dates = $stmt->get_result();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {
    $user_id = $_SESSION['user_id'];
    $pickup_location = trim($_POST['pickup_location'] ?? '');
    $drop_location = trim($_POST['drop_location'] ?? '');
    $pickup_date = $_POST['pickup_date'] ?? '';
    $return_date = $_POST['return_date'] ?? '';

    $total_days = (strtotime($return_date) - strtotime($pickup_date)) / 86400;

    if ($total_days <= 0) {
        $error = "Return date must be after pickup date";
    } else {
        // Check availability
        $check = $conn->prepare("SELECT id FROM bookings WHERE car_id = ? AND booking_status != 'Cancelled' AND (pickup_date <= ? AND return_date >= ?)");
        $check->bind_param("iss", $car_id, $return_date, $pickup_date);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $error = "Selected dates already booked";
        } else {
            $total_price = $total_days * $car['price_per_day'];
            $booking_code = "BK" . rand(10000, 99999);
            
            $ins = $conn->prepare("INSERT INTO bookings (booking_code, user_id, car_id, pickup_location, drop_location, pickup_date, return_date, total_days, total_price, payment_status, booking_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Pending')");
            $ins->bind_param("siissssid", $booking_code, $user_id, $car_id, $pickup_location, $drop_location, $pickup_date, $return_date, $total_days, $total_price);
            
            if ($ins->execute()) {
                header("Location:payment.php?booking_id=" . $conn->insert_id);
                exit();
            } else {
                $error = "Booking failed";
            }
        }
    }
}
?>
<?php include('../includes/navbar.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book <?= htmlspecialchars($car['car_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #fafafa;
            color: #171717;
            line-height: 1.5;
        }

        .page {
            min-height: 100vh;
            display: flex;
        }

        .left {
            flex: 1;
            padding: 48px 64px;
        }

        .right {
            width: 420px;
            background: #fff;
            border-left: 1px solid #e5e5e5;
            padding: 48px 40px;
        }

        .back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #737373;
            font-size: 14px;
            text-decoration: none;
            margin-bottom: 32px;
        }

        .back:hover {
            color: #171717;
        }

        .car-image {
            width: 100%;
            height: 400px;
            border-radius: 16px;
            object-fit: cover;
            background: #f5f5f5;
            margin-bottom: 32px;
        }

        .car-header {
            margin-bottom: 24px;
        }

        .car-name {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .car-meta {
            color: #737373;
            font-size: 16px;
        }

        .car-price {
            font-size: 24px;
            font-weight: 600;
        }

        .car-price span {
            color: #737373;
            font-size: 16px;
            font-weight: 400;
        }

        .section-title {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #737373;
            margin-bottom: 16px;
        }

        .features {
            display: flex;
            gap: 32px;
            margin: 32px 0;
            padding: 24px 0;
            border-top: 1px solid #e5e5e5;
            border-bottom: 1px solid #e5e5e5;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            background: #f5f5f5;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .feature-text {
            font-size: 14px;
            color: #737373;
        }

        .feature-value {
            font-size: 15px;
            font-weight: 600;
        }

        .unavailable {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .unavailable-title {
            font-size: 13px;
            font-weight: 600;
            color: #9a3412;
            margin-bottom: 8px;
        }

        .unavailable-date {
            font-size: 14px;
            color: #c2410c;
            padding: 4px 0;
        }

        .panel-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e5e5;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.2s;
        }

        input:focus {
            outline: none;
            border-color: #171717;
        }

        .dates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .price-summary {
            background: #f5f5f5;
            border-radius: 12px;
            padding: 20px;
            margin: 24px 0;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .price-row:last-child {
            margin-bottom: 0;
            padding-top: 12px;
            border-top: 1px solid #e5e5e5;
            font-weight: 600;
            font-size: 16px;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: #171717;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #000;
        }

        .error {
            background: #fef2f2;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .trust {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
            font-size: 13px;
            color: #737373;
        }

        @media (max-width: 900px) {
            .page {
                flex-direction: column;
            }
            .left {
                padding: 24px;
            }
            .right {
                width: 100%;
                border-left: none;
                border-top: 1px solid #e5e5e5;
                padding: 24px;
            }
            .car-image {
                height: 280px;
            }
        }
    </style>
</head>
<body>

<div class="page">
    <div class="left">
        <a href="../cars.php" class="back">
            ← Back to cars
        </a>
        
        <img src="../uploads/car_images/<?= htmlspecialchars($car['image']) ?>" class="car-image" alt="">
        
        <div class="car-header">
            <h1 class="car-name"><?= htmlspecialchars($car['car_name']) ?></h1>
            <p class="car-meta"><?= htmlspecialchars($car['brand']) ?> • <?= htmlspecialchars($car['year']) ?></p>
        </div>
        
        <p class="car-price">₹<?= number_format($car['price_per_day']) ?> <span>/ day</span></p>
        
        <div class="features">
            <div class="feature">
                <div class="feature-icon">⚙️</div>
                <div>
                    <div class="feature-text">Transmission</div>
                    <div class="feature-value"><?= htmlspecialchars($car['transmission']) ?></div>
                </div>
            </div>
            <div class="feature">
                <div class="feature-icon">⛽</div>
                <div>
                    <div class="feature-text">Fuel Type</div>
                    <div class="feature-value"><?= htmlspecialchars($car['fuel_type']) ?></div>
                </div>
            </div>
            <div class="feature">
                <div class="feature-icon">👥</div>
                <div>
                    <div class="feature-text">Seats</div>
                    <div class="feature-value"><?= htmlspecialchars($car['seats']) ?></div>
                </div>
            </div>
        </div>
        
        <?php if ($booked_dates->num_rows > 0): ?>
        <div class="unavailable">
            <div class="unavailable-title">Unavailable dates</div>
            <?php while ($b = $booked_dates->fetch_assoc()): ?>
                <div class="unavailable-date">
                    <?= htmlspecialchars($b['pickup_date']) ?> → <?= htmlspecialchars($b['return_date']) ?>
                </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="right">
        <h2 class="panel-title">Complete your booking</h2>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Pickup location</label>
                <input type="text" name="pickup_location" placeholder="Enter address" required>
            </div>
            
            <div class="form-group">
                <label>Drop location</label>
                <input type="text" name="drop_location" placeholder="Enter address" required>
            </div>
            
            <div class="form-group">
                <label>Trip dates</label>
                <div class="dates">
                    <input type="date" name="pickup_date" required>
                    <input type="date" name="return_date" required>
                </div>
            </div>
            
            <div class="price-summary">
                <div class="price-row">
                    <span>Car rental</span>
                    <span>₹<?= number_format($car['price_per_day']) ?> / day</span>
                </div>
                <div class="price-row">
                    <span>Taxes & fees</span>
                    <span>Included</span>
                </div>
                <div class="price-row">
                    <span>Total</span>
                    <span>Calculated at checkout</span>
                </div>
            </div>
            
            <button type="submit" name="book" class="btn">Confirm booking</button>
            
            <div class="trust">
                ✓ Free cancellation • 24/7 support
            </div>
        </form>
    </div>
</div>
<?php include '../includes/footer.php' ?>
</body>
</html>