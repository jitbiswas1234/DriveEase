<?php
session_start();
require_once '../config/database.php';
require_once '../config/base_url.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . $base_url . 'login.php?type=admin');
    exit();
}

$error = '';
$success = '';

// Get car ID
$car_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$car_id) {
    header('Location: index.php?page=cars&error=' . urlencode('Invalid car ID'));
    exit();
}

// Fetch car data
$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();
$result = $stmt->get_result();
$car = $result->fetch_assoc();
$stmt->close();

if (!$car) {
    header('Location: index.php?page=cars&error=' . urlencode('Car not found'));
    exit();
}

// Process form submission
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
        $image = $car['image']; // Keep existing image by default

        // Handle new image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            if (in_array($_FILES['image']['type'], $allowed_types)) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $new_image = 'car_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $upload_path = '../uploads/car_images/' . $new_image;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image if exists
                    if ($car['image'] && file_exists('../uploads/car_images/' . $car['image'])) {
                        unlink('../uploads/car_images/' . $car['image']);
                    }
                    $image = $new_image;
                } else {
                    $error = 'Failed to upload image.';
                }
            } else {
                $error = 'Invalid image format. Use JPG, PNG, or WebP.';
            }
        }

        // Delete image if requested
        if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
            if ($car['image'] && file_exists('../uploads/car_images/' . $car['image'])) {
                unlink('../uploads/car_images/' . $car['image']);
            }
            $image = '';
        }

        if (empty($error)) {
            $stmt = $conn->prepare("UPDATE cars SET car_name = ?, brand = ?, model = ?, year = ?, price_per_day = ?, fuel_type = ?, transmission = ?, seats = ?, car_type = ?, image = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssiissiissi", $car_name, $brand, $model, $year, $price_per_day, $fuel_type, $transmission, $seats, $car_type, $image, $status, $car_id);
            
            if ($stmt->execute()) {
                header('Location: index.php?page=cars&success=' . urlencode('Car updated successfully'));
                exit();
            } else {
                $error = 'Failed to update car. Please try again.';
            }
            $stmt->close();
        }
    }
}

$adminName = $_SESSION['admin_username'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car - DriveEase Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #000;
            --primary-light: #1a1a1a;
            --gray-50: #fafafa;
            --gray-100: #f5f5f5;
            --gray-200: #e5e5e5;
            --gray-300: #d4d4d4;
            --gray-400: #a3a3a3;
            --gray-500: #737373;
            --gray-600: #525252;
            --gray-700: #404040;
            --success: #22c55e;
            --danger: #dc2626;
            --warning: #f59e0b;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-50);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .page-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .back-btn {
            width: 45px;
            height: 45px;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            color: var(--gray-700);
        }

        .back-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-title {
            flex: 1;
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .page-title p {
            color: var(--gray-500);
            font-size: 0.9rem;
        }

        .car-preview {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: white;
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid var(--gray-200);
        }

        .car-preview-image {
            width: 80px;
            height: 60px;
            background: var(--gray-100);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .car-preview-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .car-preview-info h4 {
            font-weight: 600;
            color: var(--primary);
        }

        .car-preview-info p {
            font-size: 0.85rem;
            color: var(--gray-500);
        }

        .form-card {
            background: white;
            border-radius: 20px;
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .form-section {
            padding: 2rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .form-section-header {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.5rem;
        }

        .form-section-icon {
            width: 40px;
            height: 40px;
            background: var(--gray-100);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-600);
        }

        .form-section-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary);
        }

        .form-section-header p {
            font-size: 0.85rem;
            color: var(--gray-500);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-700);
        }

        .form-group label .required {
            color: var(--danger);
        }

        .form-control {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 0.95rem;
            transition: var(--transition);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
        }

        .form-control:disabled {
            background: var(--gray-100);
            cursor: not-allowed;
        }

        select.form-control {
            cursor: pointer;
            background: white;
        }

        /* Image Upload */
        .image-upload-container {
            display: flex;
            gap: 1.5rem;
            align-items: flex-start;
        }

        .current-image {
            width: 200px;
            flex-shrink: 0;
        }

        .current-image-preview {
            width: 100%;
            height: 150px;
            background: var(--gray-100);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        .current-image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .current-image-preview .no-image {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-400);
            font-size: 2rem;
        }

        .delete-image-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            width: 30px;
            height: 30px;
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            transition: var(--transition);
        }

        .delete-image-btn:hover {
            transform: scale(1.1);
        }

        .current-image p {
            text-align: center;
            font-size: 0.85rem;
            color: var(--gray-500);
            margin-top: 0.5rem;
        }

        .image-upload-area {
            flex: 1;
        }

        .image-upload {
            border: 2px dashed var(--gray-300);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .image-upload:hover {
            border-color: var(--primary);
            background: var(--gray-50);
        }

        .image-upload i {
            font-size: 2rem;
            color: var(--gray-400);
            margin-bottom: 0.5rem;
        }

        .image-upload p {
            color: var(--gray-500);
            font-size: 0.9rem;
        }

        .image-upload .hint {
            font-size: 0.8rem;
            color: var(--gray-400);
            margin-top: 0.3rem;
        }

        .new-image-preview {
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 12px;
            margin-top: 1rem;
            display: none;
        }

        /* Status Toggle */
        .status-options {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .status-option {
            flex: 1;
            min-width: 120px;
        }

        .status-option input {
            display: none;
        }

        .status-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
        }

        .status-option input:checked + label {
            border-color: var(--primary);
            background: var(--gray-50);
        }

        .status-option label i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .status-option label span {
            font-weight: 500;
            font-size: 0.9rem;
        }

        .status-option.available label i { color: var(--success); }
        .status-option.rented label i { color: #3b82f6; }
        .status-option.maintenance label i { color: var(--warning); }

        /* Alert */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-error {
            background: #fef2f2;
            color: var(--danger);
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #f0fdf4;
            color: var(--success);
            border: 1px solid #bbf7d0;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: space-between;
            padding: 2rem;
            background: var(--gray-50);
        }

        .btn {
            padding: 0.9rem 1.8rem;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .btn-outline {
            background: white;
            border: 2px solid var(--gray-200);
            color: var(--gray-700);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: span 1;
            }

            .image-upload-container {
                flex-direction: column;
            }

            .current-image {
                width: 100%;
            }

            .form-actions {
                flex-direction: column;
            }

            .status-options {
                flex-direction: column;
            }

            .status-option {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <a href="index.php?page=cars" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="page-title">
            <h1>Edit Car</h1>
            <p>Update vehicle information</p>
        </div>
        <div class="car-preview">
            <div class="car-preview-image">
                <?php if (!empty($car['image']) && file_exists('../uploads/car_images/' . $car['image'])): ?>
                    <img src="<?php echo $base_url; ?>uploads/car_images/<?php echo htmlspecialchars($car['image']); ?>" alt="">
                <?php else: ?>
                    <i class="fas fa-car" style="color: var(--gray-400);"></i>
                <?php endif; ?>
            </div>
            <div class="car-preview-info">
                <h4><?php echo htmlspecialchars($car['car_name']); ?></h4>
                <p><?php echo htmlspecialchars($car['brand']); ?> · ID #<?php echo $car['id']; ?></p>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form class="form-card" method="POST" enctype="multipart/form-data">
        <!-- Basic Information -->
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div>
                    <h3>Basic Information</h3>
                    <p>Car name, brand, and model details</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Car Name <span class="required">*</span></label>
                    <input type="text" name="car_name" class="form-control" value="<?php echo htmlspecialchars($car['car_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Brand <span class="required">*</span></label>
                    <input type="text" name="brand" class="form-control" value="<?php echo htmlspecialchars($car['brand']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Model</label>
                    <input type="text" name="model" class="form-control" value="<?php echo htmlspecialchars($car['model']); ?>">
                </div>
                <div class="form-group">
                    <label>Year</label>
                    <input type="number" name="year" class="form-control" value="<?php echo $car['year']; ?>" min="2000" max="<?php echo date('Y') + 1; ?>">
                </div>
            </div>
        </div>

        <!-- Specifications -->
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <div>
                    <h3>Specifications</h3>
                    <p>Technical details and features</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Fuel Type</label>
                    <select name="fuel_type" class="form-control">
                        <option value="Petrol" <?php echo $car['fuel_type'] == 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
                        <option value="Diesel" <?php echo $car['fuel_type'] == 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                        <option value="Electric" <?php echo $car['fuel_type'] == 'Electric' ? 'selected' : ''; ?>>Electric</option>
                        <option value="Hybrid" <?php echo $car['fuel_type'] == 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Transmission</label>
                    <select name="transmission" class="form-control">
                        <option value="Automatic" <?php echo $car['transmission'] == 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                        <option value="Manual" <?php echo $car['transmission'] == 'Manual' ? 'selected' : ''; ?>>Manual</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Seats</label>
                    <input type="number" name="seats" class="form-control" value="<?php echo $car['seats']; ?>" min="2" max="12">
                </div>
                <div class="form-group">
                    <label>Car Type</label>
                    <select name="car_type" class="form-control">
                        <option value="Sedan" <?php echo $car['car_type'] == 'Sedan' ? 'selected' : ''; ?>>Sedan</option>
                        <option value="SUV" <?php echo $car['car_type'] == 'SUV' ? 'selected' : ''; ?>>SUV</option>
                        <option value="Hatchback" <?php echo $car['car_type'] == 'Hatchback' ? 'selected' : ''; ?>>Hatchback</option>
                        <option value="Luxury" <?php echo $car['car_type'] == 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
                        <option value="Sports" <?php echo $car['car_type'] == 'Sports' ? 'selected' : ''; ?>>Sports</option>
                        <option value="Van" <?php echo $car['car_type'] == 'Van' ? 'selected' : ''; ?>>Van</option>
                        <option value="Truck" <?php echo $car['car_type'] == 'Truck' ? 'selected' : ''; ?>>Truck</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div>
                    <h3>Pricing</h3>
                    <p>Set the rental price per day</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Price Per Day ($) <span class="required">*</span></label>
                    <input type="number" name="price_per_day" class="form-control" value="<?php echo $car['price_per_day']; ?>" step="0.01" min="0" required>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon">
                    <i class="fas fa-toggle-on"></i>
                </div>
                <div>
                    <h3>Status</h3>
                    <p>Set the current availability status</p>
                </div>
            </div>
            <div class="status-options">
                <div class="status-option available">
                    <input type="radio" name="status" value="Available" id="status_available" <?php echo $car['status'] == 'Available' ? 'checked' : ''; ?>>
                    <label for="status_available">
                        <i class="fas fa-check-circle"></i>
                        <span>Available</span>
                    </label>
                </div>
                <div class="status-option rented">
                    <input type="radio" name="status" value="Rented" id="status_rented" <?php echo $car['status'] == 'Rented' ? 'checked' : ''; ?>>
                    <label for="status_rented">
                        <i class="fas fa-key"></i>
                        <span>Rented</span>
                    </label>
                </div>
                <div class="status-option maintenance">
                    <input type="radio" name="status" value="Maintenance" id="status_maintenance" <?php echo $car['status'] == 'Maintenance' ? 'checked' : ''; ?>>
                    <label for="status_maintenance">
                        <i class="fas fa-wrench"></i>
                        <span>Maintenance</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Car Image -->
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon">
                    <i class="fas fa-image"></i>
                </div>
                <div>
                    <h3>Car Image</h3>
                    <p>Update the car photo</p>
                </div>
            </div>
            <div class="image-upload-container">
                <div class="current-image">
                    <div class="current-image-preview">
                        <?php if (!empty($car['image']) && file_exists('../uploads/car_images/' . $car['image'])): ?>
                            <img src="<?php echo $base_url; ?>uploads/car_images/<?php echo htmlspecialchars($car['image']); ?>" alt="Current car image">
                            <button type="button" class="delete-image-btn" onclick="deleteCurrentImage()" title="Remove image">
                                <i class="fas fa-times"></i>
                            </button>
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-car"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p>Current Image</p>
                    <input type="hidden" name="delete_image" id="deleteImageInput" value="0">
                </div>
                <div class="image-upload-area">
                    <label class="image-upload" id="imageUploadLabel">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click to upload new image</p>
                        <p class="hint">JPG, PNG, WebP (Max 5MB)</p>
                        <input type="file" name="image" id="imageInput" accept="image/*" style="display: none;">
                        <img id="newImagePreview" class="new-image-preview">
                    </label>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                <i class="fas fa-trash"></i> Delete Car
            </button>
            <div style="display: flex; gap: 1rem;">
                <a href="index.php?page=cars" class="btn btn-outline">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Image preview
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('newImagePreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
            
            // Update label text
            document.querySelector('.image-upload p').textContent = 'New image selected';
        }
        reader.readAsDataURL(file);
    }
});

// Delete current image
function deleteCurrentImage() {
    if (confirm('Are you sure you want to remove the current image?')) {
        document.getElementById('deleteImageInput').value = '1';
        document.querySelector('.current-image-preview').innerHTML = '<div class="no-image"><i class="fas fa-car"></i></div>';
    }
}

// Confirm delete car
function confirmDelete() {
    if (confirm('Are you sure you want to delete this car? This action cannot be undone.')) {
        window.location.href = 'api/delete_car.php?id=<?php echo $car_id; ?>&redirect=1';
    }
}
</script>

</body>
</html>