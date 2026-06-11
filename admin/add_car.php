<?php
session_start();
require_once '../config/database.php';
require_once '../config/base_url.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . $base_url . 'login.php?type=admin');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_name = trim($_POST['car_name'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = intval($_POST['year'] ?? date('Y'));
    $price_per_day = floatval($_POST['price_per_day'] ?? 0);
    $fuel_type = $_POST['fuel_type'] ?? '';
    $transmission = $_POST['transmission'] ?? '';
    $seats = intval($_POST['seats'] ?? 5);
    $car_type = $_POST['car_type'] ?? '';
    $status = $_POST['status'] ?? 'Available';

    // Validation
    if (empty($car_name) || empty($brand) || empty($price_per_day)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Handle image upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            if (in_array($_FILES['image']['type'], $allowed_types)) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image = 'car_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $upload_path = '../uploads/car_images/' . $image;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $error = 'Failed to upload image.';
                }
            } else {
                $error = 'Invalid image format. Use JPG, PNG, or WebP.';
            }
        }

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO cars (car_name, brand, model, year, price_per_day, fuel_type, transmission, seats, car_type, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiissiiss", $car_name, $brand, $model, $year, $price_per_day, $fuel_type, $transmission, $seats, $car_type, $image, $status);
            
            if ($stmt->execute()) {
                header('Location: index.php?page=cars&success=Car added successfully');
                exit();
            } else {
                $error = 'Failed to add car. Please try again.';
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Car - DriveEase Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #000;
            --gray-50: #fafafa;
            --gray-100: #f5f5f5;
            --gray-200: #e5e5e5;
            --gray-500: #737373;
            --gray-700: #404040;
            --success: #22c55e;
            --danger: #dc2626;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-50);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .back-btn {
            width: 40px;
            height: 40px;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: var(--primary);
            color: white;
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .page-title p {
            color: var(--gray-500);
            font-size: 0.9rem;
        }

        .form-card {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--gray-200);
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-group label .required {
            color: var(--danger);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--gray-200);
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
        }

        .image-upload {
            border: 2px dashed var(--gray-200);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .image-upload:hover {
            border-color: var(--primary);
            background: var(--gray-50);
        }

        .image-upload i {
            font-size: 2rem;
            color: var(--gray-500);
            margin-bottom: 0.5rem;
        }

        .image-upload p {
            color: var(--gray-500);
            font-size: 0.9rem;
        }

        .image-preview {
            max-width: 200px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-top: 1rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-error {
            background: #fef2f2;
            color: var(--danger);
            border: 1px solid #fecaca;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .btn-outline {
            background: white;
            border: 1px solid var(--gray-200);
            color: var(--gray-700);
        }

        .btn-outline:hover {
            border-color: var(--primary);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <button class="back-btn" onclick="window.location.href='index.php?page=cars'">
            <i class="fas fa-arrow-left"></i>
        </button>
        <div class="page-title">
            <h1>Add New Car</h1>
            <p>Add a new vehicle to your fleet</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form class="form-card" method="POST" enctype="multipart/form-data">
        <div class="form-section">
            <h3>Basic Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Car Name <span class="required">*</span></label>
                    <input type="text" name="car_name" class="form-control" placeholder="e.g., Camry" required>
                </div>
                <div class="form-group">
                    <label>Brand <span class="required">*</span></label>
                    <input type="text" name="brand" class="form-control" placeholder="e.g., Toyota" required>
                </div>
                <div class="form-group">
                    <label>Model</label>
                    <input type="text" name="model" class="form-control" placeholder="e.g., XLE">
                </div>
                <div class="form-group">
                    <label>Year</label>
                    <input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>" min="2000" max="<?php echo date('Y') + 1; ?>">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Specifications</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Fuel Type</label>
                    <select name="fuel_type" class="form-control">
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="Electric">Electric</option>
                        <option value="Hybrid">Hybrid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Transmission</label>
                    <select name="transmission" class="form-control">
                        <option value="Automatic">Automatic</option>
                        <option value="Manual">Manual</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Seats</label>
                    <input type="number" name="seats" class="form-control" value="5" min="2" max="12">
                </div>
                <div class="form-group">
                    <label>Car Type</label>
                    <select name="car_type" class="form-control">
                        <option value="Sedan">Sedan</option>
                        <option value="SUV">SUV</option>
                        <option value="Hatchback">Hatchback</option>
                        <option value="Luxury">Luxury</option>
                        <option value="Sports">Sports</option>
                        <option value="Van">Van</option>
                        <option value="Truck">Truck</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Pricing & Status</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Price Per Day ($) <span class="required">*</span></label>
                    <input type="number" name="price_per_day" class="form-control" placeholder="0.00" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="Available">Available</option>
                        <option value="Rented">Rented</option>
                        <option value="Maintenance">Maintenance</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Car Image</h3>
            <div class="form-group">
                <label class="image-upload" id="imageUploadLabel">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to upload image</p>
                    <p style="font-size: 0.8rem; margin-top: 0.3rem;">JPG, PNG, WebP (Max 5MB)</p>
                    <input type="file" name="image" id="imageInput" accept="image/*" style="display: none;">
                    <img id="imagePreview" class="image-preview" style="display: none;">
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn btn-outline" onclick="window.location.href='index.php?page=cars'">
                Cancel
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Car
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>