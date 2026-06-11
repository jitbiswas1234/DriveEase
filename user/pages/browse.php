<?php
// 1. Include the navbar at the very top, before any HTML starts
require_once '../config/database.php';

$query="SELECT * FROM cars ORDER BY created_at DESC";
$result=mysqli_query($conn,$query);
?>

<style>
    :root {
        --primary: #0a0a0a;
        --primary-light: #1f1f1f;
        --secondary: #ffffff;
        --accent: #ff3b3b;
        --success: #10b981;
        --warning: #f59e0b;
        --info: #3b82f6;
        --danger: #ef4444;
        --gray-50: #f8fafc;
        --gray-100: #f1f5f9;
        --gray-200: #e2e8f0;
        --gray-300: #cbd5e1;
        --gray-400: #94a3b8;
        --gray-500: #64748b;
        --gray-600: #475569;
        --gray-700: #334155;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.08), 0 4px 6px -4px rgb(0 0 0 / 0.04);
        --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--gray-50);
        color: var(--gray-700);
        overflow-x: hidden;
    }

    a { text-decoration: none; color: inherit; }

    .page-container {
        padding: 2.5rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        margin-bottom: 2.5rem;
        position: relative;
    }

    .page-title {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--primary);
        margin-bottom: 0.5rem;
        letter-spacing: -0.5px;
        position: relative;
        display: inline-block;
    }

    .page-title::after {
        content: '';
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 60px;
        height: 4px;
        background: var(--accent);
        border-radius: 2px;
    }

    .page-subtitle {
        font-size: 1.1rem;
        color: var(--gray-500);
        font-weight: 500;
        margin-left: 2px;
    }

    .cars-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
    }

    .car-card {
        background: white;
        border-radius: 16px;
        border: 1px solid var(--gray-200);
        overflow: hidden;
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
    }

    .car-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
        border-color: var(--accent);
    }

    .car-image-container {
        height: 180px;
        background: var(--gray-100);
        position: relative;
        overflow: hidden;
    }

    .car-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .car-card:hover .car-image {
        transform: scale(1.05);
    }

    .car-info {
        padding: 1.5rem;
    }

    .car-name {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }

    .car-brand {
        font-size: 0.9rem;
        color: var(--gray-500);
        margin-bottom: 1rem;
    }

    .car-price {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--primary);
        margin-bottom: 1rem;
    }

    .car-price span {
        font-size: 0.85rem;
        color: var(--gray-400);
        font-weight: 500;
    }

    .car-status {
        display: inline-block;
        padding: 0.4rem 0.8rem;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .car-status.available {
        background: rgba(16, 185, 129, 0.15);
        color: var(--success);
    }

    .car-status.booked {
        background: rgba(239, 68, 68, 0.15);
        color: var(--danger);
    }

    .car-actions {
        display: flex;
        gap: 1rem;
    }

    .btn {
        flex: 1;
        padding: 0.8rem;
        border-radius: 12px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-outline {
        background: transparent;
        border: 1px solid var(--gray-300);
        color: var(--gray-700);
    }

    .btn-outline:hover {
        border-color: var(--primary);
        color: var(--primary);
        background: var(--gray-50);
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--accent);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--gray-500);
        background: linear-gradient(135deg, rgba(248, 250, 252, 0.5) 0%, rgba(255, 59, 59, 0.02) 100%);
        border-radius: 18px;
        border: 1px dashed var(--gray-200);
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--gray-300);
        margin-bottom: 1.2rem;
        display: inline-block;
    }

    .empty-state h4 {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .page-container {
            padding: 1.5rem;
        }

        .cars-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
    }

    @media (max-width: 480px) {
        .car-actions {
            flex-direction: column;
        }
    }
</style>

<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">Browse Cars</h1>
        <p class="page-subtitle">Find your perfect ride</p>
    </div>

    <div class="cars-grid">
        <?php
        if(mysqli_num_rows($result) > 0)
        {
            while($car = mysqli_fetch_assoc($result))
            {
        ?>
            <div class="car-card">
                <div class="car-image-container">
                    <?php if(!empty($car['image'])): ?>
                        <img src="../uploads/car_images/<?php echo htmlspecialchars($car['image']); ?>" 
                             alt="<?php echo htmlspecialchars($car['car_name']); ?>" 
                             class="car-image">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--gray-100);">
                            <i class="fas fa-car" style="font-size: 3rem; color: var(--gray-300);"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="car-info">
                    <h3 class="car-name"><?php echo htmlspecialchars($car['car_name']); ?></h3>
                    <p class="car-brand">
                        <?php echo htmlspecialchars($car['brand']); ?> · 
                        <?php echo htmlspecialchars($car['model']); ?>
                    </p>
                    
                    <div class="car-price">
                        ₹<?php echo number_format($car['price_per_day']); ?>
                        <span>/day</span>
                    </div>
                    
                    <span class="car-status <?php echo strtolower($car['status']); ?>">
                        <?php echo $car['status']; ?>
                    </span>
                    
                    <div class="car-actions">
                        <a href="show_details.php?id=<?php echo $car['id']; ?>" class="btn btn-outline">
                            <i class="fas fa-info-circle"></i> Details
                        </a>
                        <?php if($car['status']=="Available"): ?>
                            <a href="../booking/book_car.php?id=<?php echo $car['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-calendar-plus"></i> Book
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php
            }
        }
        else
        {
            echo '<div class="empty-state">
                <i class="fas fa-car-side"></i>
                <h4>No cars available</h4>
                <p>Check back soon for available vehicles!</p>
            </div>';
        }
        ?>
    </div>
</div>