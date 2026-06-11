<?php

session_start();

require_once '../config/database.php';
require_once '../config/base_url.php';

/* ===== AUTH CHECK ===== */

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

/* ===== GET CAR ID ===== */

$car_id = $_GET['id'] ?? 0;
$car_id = (int)$car_id;

if($car_id <= 0){
    die("Invalid car");
}

/* ===== GET CAR ===== */

$stmt = $conn->prepare("SELECT * FROM cars WHERE id=?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();

if(!$car){
    die("Car not found");
}

/* ===== SIMILAR CARS ===== */

$similar_stmt = $conn->prepare(
    "SELECT * FROM cars 
    WHERE car_type=? 
    AND id!=?
    AND status='Available'
    LIMIT 4"
);

$similar_stmt->bind_param("si", $car['car_type'], $car_id);
$similar_stmt->execute();
$similar_cars = $similar_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($car['car_name']); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Black and White Theme */
            --primary: #000000;
            --secondary: #1a1a1a;
            --bg-light: #f8f9fa;
            --border-color: #e5e5e5;
            --text-main: #000000;
            --text-muted: #666666;
            
            /* Status Colors (kept for functionality) */
            --status-available: #198754;
            --status-unavailable: #dc3545;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            color: var(--text-main);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Adjust global navbar spacing if needed */
        nav {
            background: #fff;
        }

        /* MAIN LAYOUT */
        .main-content {
            flex: 1;
            padding: 40px 0 80px;
        }

        /* CAR IMAGE */
        .car-image-container {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: var(--bg-light);
            border: 1px solid var(--border-color);
        }

        .car-img {
            width: 100%;
            height: 500px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .car-image-container:hover .car-img {
            transform: scale(1.02);
        }

        .status-tag {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .status-available { background: var(--status-available); }
        .status-unavailable { background: var(--status-unavailable); }

        /* CAR DETAILS BOX */
        .details-box {
            padding-left: 20px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 20px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: var(--primary);
        }

        .car-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1.2;
            margin-bottom: 5px;
            letter-spacing: -1px;
        }

        .car-meta {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin-bottom: 30px;
            font-weight: 400;
        }

        .price-tag {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
        }

        .price-tag span {
            font-size: 1rem;
            font-weight: 400;
            color: var(--text-muted);
        }

        /* FEATURES GRID */
        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .feature-icon {
            width: 45px;
            height: 45px;
            background: var(--bg-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.1rem;
            border: 1px solid var(--border-color);
        }

        .feature-text small {
            display: block;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .feature-text strong {
            display: block;
            font-size: 1rem;
            font-weight: 600;
        }

        /* BUTTONS */
        .btn-black {
            background: #000000;
            color: #ffffff;
            width: 100%;
            padding: 18px;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 2px solid #000000;
            transition: all 0.3s ease;
        }

        .btn-black:hover {
            background: #ffffff;
            color: #000000;
        }
        
        .btn-black:disabled {
            background: #ccc;
            border-color: #ccc;
            cursor: not-allowed;
        }

        /* SIMILAR CARS */
        .similar-section {
            margin-top: 80px;
            border-top: 1px solid var(--border-color);
            padding-top: 40px;
        }

        .similar-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .similar-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        .similar-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            filter: grayscale(20%);
            transition: filter 0.3s;
        }

        .similar-card:hover img {
            filter: grayscale(0%);
        }

        .similar-body {
            padding: 15px;
        }

        .similar-title {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .similar-price {
            font-weight: 600;
            color: var(--text-muted);
        }
    </style>
</head>

<body>

    <!-- GLOBAL NAVBAR -->
    <?php require_once '../includes/navbar.php'; ?>

    <div class="main-content">
        <div class="container">
            
            <div class="row g-5">
                
                <!-- Left: Image -->
                <div class="col-lg-7">
                    <div class="car-image-container">
                        <span class="status-tag <?php echo $car['status']=='Available' ? 'status-available' : 'status-unavailable'; ?>">
                            <?php echo $car['status']; ?>
                        </span>

                        <?php
                        $image = !empty($car['image']) 
                            ? '../uploads/car_images/'.$car['image'] 
                            : '../assets/images/no-car.png';
                        ?>
                        <img src="<?php echo $image; ?>" class="car-img" alt="Car Image">
                    </div>
                </div>

                <!-- Right: Info -->
                <div class="col-lg-5">
                    <div class="details-box">
                        <a href="cars.php" class="back-link">
                            <i class="fa fa-arrow-left me-2"></i> Back to Fleet
                        </a>

                        <h1 class="car-title"><?php echo $car['car_name']; ?></h1>
                        <p class="car-meta">
                            <?php echo $car['brand']." ".$car['model']." / ".$car['year']; ?>
                        </p>

                        <div class="price-tag">
                            ₹<?php echo number_format($car['price_per_day'], 2); ?>
                            <span>/ day</span>
                        </div>

                        <div class="features-grid">
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fa fa-gas-pump"></i></div>
                                <div class="feature-text">
                                    <small>Fuel Type</small>
                                    <strong><?php echo $car['fuel_type']; ?></strong>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fa fa-gear"></i></div>
                                <div class="feature-text">
                                    <small>Transmission</small>
                                    <strong><?php echo $car['transmission']; ?></strong>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fa fa-user-group"></i></div>
                                <div class="feature-text">
                                    <small>Capacity</small>
                                    <strong><?php echo $car['seats']; ?> Seats</strong>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fa fa-car-side"></i></div>
                                <div class="feature-text">
                                    <small>Category</small>
                                    <strong><?php echo $car['car_type']; ?></strong>
                                </div>
                            </div>
                        </div>

                        <?php if($car['status']=="Available"): ?>
                            <a href="../booking/book_car.php?id=<?php echo $car['id']; ?>" style="text-decoration: none;">
                                <button class="btn-black">
                                    Book Now
                                </button>
                            </a>
                        <?php else: ?>
                            <button class="btn-black" disabled>
                                Currently Unavailable
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Similar Cars Section -->
            <?php if($similar_cars->num_rows > 0): ?>
            <div class="similar-section">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold m-0">Similar Vehicles</h3>
                </div>

                <div class="row g-4">
                    <?php while($row = $similar_cars->fetch_assoc()): ?>
                        <?php $img = !empty($row['image']) ? '../uploads/car_images/'.$row['image'] : '../assets/images/no-car.png'; ?>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="car_details.php?id=<?php echo $row['id']; ?>" class="text-decoration-none text-dark">
                                <div class="similar-card">
                                    <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($row['car_name']); ?>">
                                    <div class="similar-body">
                                        <div class="similar-title"><?php echo $row['car_name']; ?></div>
                                        <div class="similar-price">₹<?php echo number_format($row['price_per_day'], 2); ?>/day</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- GLOBAL FOOTER -->
    <?php require_once '../includes/footer.php'; ?>

</body>
</html>

<?php $conn->close(); ?>