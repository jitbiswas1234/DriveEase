<?php
session_start();
require_once("../config/database.php");

// 1. Validate & Secure Input
$booking_id = filter_input(INPUT_GET, 'booking_id', FILTER_SANITIZE_NUMBER_INT);

if (!$booking_id) {
    header("Location: ../cars.php");
    exit();
}

// 2. Secure Query using Prepared Statements
$stmt = $conn->prepare("
    SELECT b.*, c.car_name, c.brand, c.image 
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if (!$booking) {
    header("Location: ../cars.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
    <style>
        * { box-sizing: border-box; font-family: Inter, system-ui, -apple-system, sans-serif; }
        body { background: #f9fafb; color: #111827; margin: 0; padding: 40px 20px; }
        
        .container { max-width: 500px; margin: 0 auto; }
        
        .card { 
            background: #fff; 
            border: 1px solid #e5e7eb; 
            border-radius: 12px; 
            padding: 32px; 
        }

        .success-header { text-align: center; margin-bottom: 32px; }
        .icon { font-size: 40px; color: #059669; margin-bottom: 12px; }
        h1 { font-size: 20px; margin: 0; }
        
        .car-image { 
            width: 100%; height: 200px; object-fit: cover; 
            border-radius: 8px; background: #f3f4f6; margin-bottom: 24px; 
        }

        .details { border-top: 1px solid #f3f4f6; padding-top: 20px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; }
        .row label { color: #6b7280; }
        .row span { font-weight: 600; }

        .total { 
            border-top: 2px solid #111827; margin-top: 16px; 
            padding-top: 16px; display: flex; justify-content: space-between;
            font-weight: 700; font-size: 16px;
        }

        .actions { margin-top: 32px; display: flex; flex-direction: column; gap: 12px; }
        .btn { 
            text-align: center; padding: 12px; border-radius: 8px; 
            text-decoration: none; font-size: 14px; font-weight: 600; 
        }
        .btn-black { background: #111827; color: #fff; }
        .btn-gray { background: #f3f4f6; color: #374151; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="success-header">
            <div class="icon">✓</div>
            <h1>Booking Confirmed</h1>
        </div>

        <img src="../uploads/car_images/<?php echo htmlspecialchars($booking['image']); ?>" class="car-image">

        <div class="details">
            <div class="row"><label>Car</label> <span><?php echo htmlspecialchars($booking['car_name']); ?></span></div>
            <div class="row"><label>Booking Code</label> <span><?php echo htmlspecialchars($booking['booking_code']); ?></span></div>
            <div class="row"><label>Pick-up</label> <span><?php echo htmlspecialchars($booking['pickup_date']); ?></span></div>
            <div class="row"><label>Return</label> <span><?php echo htmlspecialchars($booking['return_date']); ?></span></div>
            <div class="total">
                <span>Total Paid</span>
                <span>₹<?php echo number_format($booking['total_price']); ?></span>
            </div>
        </div>

        <div class="actions">
            <a href="../user/invoice_pdf.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-gray">Download Invoice</a>
            <a href="../user/index.php" class="btn btn-black">Back to Dashboard</a>
        </div>
    </div>
</div>

</body>
</html>