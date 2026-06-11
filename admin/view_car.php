<?php
session_start();

// Database configuration (Keep your existing connection logic)
$host = 'localhost';
$db   = 'car_rental';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($carId <= 0) { header("Location: pages/cars.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->execute([$carId]);
$car = $stmt->fetch();

if (!$car) { die("Car not found"); }

$imagePath = '../uploads/car_images/' . $car['image'];
$imageExists = file_exists($imagePath);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($car['car_name']) ?> | Premium Rental</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --danger: #ef4444;
            --success: #22c55e;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg);
            color: var(--text-main);
            line-height: 1.6;
        }

        .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }

        /* Navigation */
        .top-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .back-link { 
            text-decoration: none; color: var(--text-muted); font-weight: 500; 
            display: flex; align-items: center; gap: 5px; transition: color 0.2s;
        }
        .back-link:hover { color: var(--primary); }

        /* Main Layout */
        .main-grid { 
            display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 30px; 
            background: var(--card-bg); border-radius: 24px; padding: 40px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        }

        /* Left: Image Section */
        .image-gallery { position: relative; }
        .main-img { 
            width: 100%; border-radius: 20px; object-fit: cover; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .status-pill {
            position: absolute; top: 20px; left: 20px;
            padding: 6px 16px; border-radius: 50px; font-size: 0.85rem;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .status-available { background: var(--success); color: white; }
        .status-rented { background: #f59e0b; color: white; }

        /* Right: Info Section */
        .car-info { display: flex; flex-direction: column; }
        .brand-tag { color: var(--primary); font-weight: 700; text-transform: uppercase; font-size: 0.9rem; letter-spacing: 1px; }
        .car-name { font-size: 2.5rem; font-weight: 800; margin: 5px 0 15px; letter-spacing: -1px; }
        
        .price-card { 
            background: #f1f5f9; padding: 20px; border-radius: 16px; 
            display: flex; align-items: baseline; gap: 8px; margin-bottom: 25px;
        }
        .price-val { font-size: 2rem; font-weight: 800; color: var(--primary); }
        .price-unit { color: var(--text-muted); font-weight: 500; }

        /* Specification Grid */
        .spec-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px; }
        .spec-item { 
            padding: 15px; border: 1px solid #e2e8f0; border-radius: 12px;
            display: flex; flex-direction: column;
        }
        .spec-label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; }
        .spec-value { font-weight: 600; font-size: 1rem; }

        /* Buttons */
        .btn-group { display: flex; gap: 15px; margin-top: auto; }
        .btn { 
            flex: 1; padding: 16px; border-radius: 12px; border: none;
            font-weight: 700; cursor: pointer; text-align: center;
            text-decoration: none; transition: all 0.2s; font-size: 1rem;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .btn-outline { background: transparent; border: 2px solid #e2e8f0; color: var(--text-main); }
        .btn-outline:hover { border-color: var(--text-main); background: #f8fafc; }
        .btn-danger { color: var(--danger); font-size: 0.9rem; margin-top: 15px; background: transparent; }

        @media (max-width: 850px) {
            .main-grid { grid-template-columns: 1fr; padding: 20px; }
            .car-name { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="top-nav">
        <a href="index.php?page=cars" class="back-link">← Explore more cars</a>
        <span style="color: var(--text-muted); font-size: 0.9rem;">ID: #<?= $car['id'] ?></span>
    </div>

    <div class="main-grid">
        <div class="image-gallery">
            <span class="status-pill status-<?= strtolower($car['status']) ?>">
                <?= htmlspecialchars($car['status']) ?>
            </span>
            <?php if ($imageExists): ?>
                <img src="<?= htmlspecialchars($imagePath) ?>" class="main-img" alt="Car Image">
            <?php else: ?>
                <div style="height:400px; background:#e2e8f0; border-radius:20px; display:flex; align-items:center; justify-content:center; font-size:3rem;">🚗</div>
            <?php endif; ?>
        </div>

        <div class="car-info">
            <span class="brand-tag"><?= htmlspecialchars($car['brand']) ?></span>
            <h1 class="car-name"><?= htmlspecialchars($car['car_name']) ?></h1>
            
            <div class="price-card">
                <span class="price-val">₹<?= number_format($car['price_per_day'], 0) ?></span>
                <span class="price-unit">/ day</span>
            </div>

            <div class="spec-grid">
                <div class="spec-item">
                    <span class="spec-label">Year</span>
                    <span class="spec-value"><?= htmlspecialchars($car['year']) ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Transmission</span>
                    <span class="spec-value"><?= htmlspecialchars($car['transmission']) ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Fuel</span>
                    <span class="spec-value"><?= htmlspecialchars($car['fuel_type']) ?></span>
                </div>
                <div class="spec-item">
                    <span class="spec-label">Seats</span>
                    <span class="spec-value"><?= htmlspecialchars($car['seats']) ?> Person</span>
                </div>
            </div>

            <div class="btn-group">
                <a href="edit_car.php?id=<?= $car['id'] ?>" class="btn btn-outline">Modify Details</a>
                <?php if ($car['status'] == 'Available'): ?>
                    <form method="post" action="api/delete_car.php" onsubmit="return confirm('Archive this vehicle?');">
                <input type="hidden" name="id" value="<?= $car['id'] ?>">
                <button type="submit" class="btn btn-danger">Remove from fleet</button>
            </form>
                <?php endif; ?>
            </div>

            
        </div>
    </div>
</div>

</body>
</html>