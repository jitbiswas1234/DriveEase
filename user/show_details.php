<?php
session_start();

// --- 1. Database Connection ---
$host = 'localhost';
$db   = 'car_rental';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (\PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}

// --- 2. Fetch Car Details ---
$carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($carId <= 0) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$carId]);
$car = $stmt->fetch();

if (!$car) { die("Car not found."); }

$imagePath = '../uploads/car_images/' . $car['image'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['car_name']) ?> | Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --accent: #2563eb;
            --dark: #0f172a;
            --light-bg: #f8fafc;
            --card-bg: #ffffff;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --primary: #0a0a0a;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--light-bg); color: var(--dark); line-height: 1.6; }

        .container { max-width: 1100px; margin: 50px auto; padding: 0 20px; }
        .grid-layout { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 40px; }

        /* Left Side: Visuals */
        .car-card { background: var(--card-bg); border-radius: 24px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.04); transition: transform 0.3s ease; }
        .car-card:hover { transform: translateY(-5px); }
        .image-container { 
            position: relative; width: 100%; height: 450px; background: #f1f5f9; 
            display: flex; align-items: center; justify-content: center; overflow: hidden;
            border-bottom: 1px solid #e2e8f0;
        }
        .image-container img { width: 100%; height: 100%; object-fit: contain; padding: 20px; transition: transform 0.5s ease; }
        .car-card:hover .image-container img { transform: scale(1.05); }
        
        .details-padding { padding: 35px; }
        .car-meta { color: var(--accent); font-weight: 700; text-transform: uppercase; font-size: 0.9rem; letter-spacing: 1px; animation: fadeIn 0.8s ease; }
        .car-title { font-size: 2.5rem; font-weight: 800; margin-bottom: 20px; animation: slideInLeft 0.8s ease; }

        /* Specs Styling */
        .spec-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px; }
        .spec-box { background: #f1f5f9; padding: 15px; border-radius: 16px; text-align: center; transition: all 0.3s ease; }
        .spec-box:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .spec-label { font-size: 0.75rem; color: #64748b; text-transform: uppercase; display: block; }
        .spec-val { font-weight: 700; font-size: 1rem; color: var(--primary); }

        /* Right Side: Information Sidebar */
        .info-sidebar { 
            background: var(--card-bg); padding: 35px; border-radius: 24px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.08); height: fit-content;
            position: sticky; top: 30px; transition: all 0.3s ease;
        }
        .info-sidebar:hover { box-shadow: 0 25px 60px rgba(0,0,0,0.12); }

        .price-badge { 
            display: inline-block; background: #eff6ff; color: var(--accent); 
            padding: 5px 15px; border-radius: 50px; font-weight: 700; font-size: 0.9rem; margin-bottom: 10px;
            animation: pulse 2s infinite;
        }

        .status-indicator {
            display: flex; align-items: center; gap: 10px; margin-top: 25px;
            padding: 15px; border-radius: 12px; font-weight: 600;
            animation: fadeIn 1s ease;
            background: <?= $car['status'] == 'Available' ? '#dcfce7' : '#fee2e2' ?>;
            color: <?= $car['status'] == 'Available' ? '#166534' : '#991b1b' ?>;
        }

        .dot { 
            height: 10px; width: 10px; border-radius: 50%; 
            background-color: currentColor; 
            animation: pulse 1.5s infinite;
        }

        .back-btn {
            display: block; width: 100%; text-align: center; padding: 15px;
            background: var(--dark); color: white; text-decoration: none;
            border-radius: 12px; font-weight: 700; margin-top: 20px;
            transition: all 0.3s ease;
        }
        .back-btn:hover { 
            background: var(--accent); 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInLeft {
            from { 
                opacity: 0;
                transform: translateX(-30px);
            }
            to { 
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        /* Responsive */
        @media (max-width: 900px) { 
            .grid-layout { grid-template-columns: 1fr; }
            .spec-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="grid-layout">
        <div class="car-card">
            <div class="image-container">
                <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= $car['car_name'] ?>">
            </div>
            <div class="details-padding">
                <span class="car-meta"><?= htmlspecialchars($car['brand']) ?> • <?= htmlspecialchars($car['year']) ?></span>
                <h1 class="car-title"><?= htmlspecialchars($car['car_name']) ?></h1>
                
                <h3 style="margin-bottom: 10px; animation: fadeIn 0.8s ease;">Vehicle Overview</h3>
                <p style="color:#64748b; animation: fadeIn 1s ease;">The <?= htmlspecialchars($car['brand']) ?> <?= htmlspecialchars($car['car_name']) ?> is one of our premium offerings. Known for its <?= htmlspecialchars($car['transmission']) ?> transmission and efficient <?= htmlspecialchars($car['fuel_type']) ?> engine, it provides a seamless driving experience.</p>
                
                <div class="spec-grid">
                    <div class="spec-box">
                        <span class="spec-label">Transmission</span>
                        <span class="spec-val"><?= $car['transmission'] ?></span>
                    </div>
                    <div class="spec-box">
                        <span class="spec-label">Fuel Type</span>
                        <span class="spec-val"><?= $car['fuel_type'] ?></span>
                    </div>
                    <div class="spec-box">
                        <span class="spec-label">Capacity</span>
                        <span class="spec-val"><?= $car['seats'] ?> Seats</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="info-sidebar">
            <span class="price-badge">Rental Rate</span>
            <div style="font-size: 2.5rem; font-weight: 800; animation: slideInRight 0.8s ease;">₹<?= number_format($car['price_per_day'], 0) ?> <span style="font-size: 1rem; color: #64748b; font-weight: 400;">/ day</span></div>
            
            <p style="margin-top: 20px; color: #64748b; font-size: 0.9rem; animation: fadeIn 1.2s ease;">
                This vehicle is part of our standard fleet. Price includes basic insurance and 24/7 roadside assistance.
            </p>

            <div class="status-indicator">
                <span class="dot"></span>
                Currently <?= $car['status'] ?>
            </div>

            <hr style="margin: 25px 0; border: 0; border-top: 1px solid #f1f5f9; animation: fadeIn 1.5s ease;">

            <p style="font-weight: 700; font-size: 0.9rem; margin-bottom: 10px; animation: fadeIn 1.2s ease;">Want to book this car?</p>
            <p style="font-size: 0.85rem; color: #64748b; animation: fadeIn 1.4s ease;">Please login to your account or contact our support team to proceed with the reservation.</p>

            <a href="index.php" class="back-btn">Return to Fleet</a>
        </div>
    </div>
</div>

<script>
    // Add interactive text color change on hover
    document.querySelectorAll('.spec-val').forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.style.color = '#2563eb';
        });
        element.addEventListener('mouseleave', function() {
            this.style.color = '#0f172a';
        });
    });
</script>

</body>
</html>