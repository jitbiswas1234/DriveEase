<?php
// User stats
$total_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE user_id = $user_id"))['count'];
$active_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE user_id = $user_id AND booking_status IN ('Pending', 'Confirmed')"))['count'];
$completed_trips = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE user_id = $user_id AND booking_status = 'Completed'"))['count'];
$total_spent = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_price) as total FROM bookings WHERE user_id = $user_id AND payment_status = 'Paid'"))['total'] ?? 0;

// Recent bookings
$recent_bookings = mysqli_query($conn, "
    SELECT b.*, c.car_name, c.brand, c.image 
    FROM bookings b 
    LEFT JOIN cars c ON b.car_id = c.id 
    WHERE b.user_id = $user_id 
    ORDER BY b.created_at DESC 
    LIMIT 5
");

// Recommended cars
$recommended_cars = mysqli_query($conn, "SELECT * FROM cars WHERE status = 'Available' ORDER BY RAND() LIMIT 4");
?>

<style>
    /* Inherits variables from parent layout: --primary, --accent, --gray-*, etc. */
    
    .welcome-banner {
        background: linear-gradient(135deg, #0f172a 0%, #020617 100%);
        border-radius: 24px;
        padding: 3rem;
        color: white;
        margin-bottom: 2.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.05);
        z-index: 1;
    }

    /* Decorative background glowing orbs */
    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(255, 59, 59, 0.15) 0%, rgba(0,0,0,0) 70%);
        border-radius: 50%;
        z-index: -1;
    }
    
    .welcome-banner::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, rgba(0,0,0,0) 70%);
        border-radius: 50%;
        z-index: -1;
    }

    .welcome-banner h2 {
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 0.8rem;
        letter-spacing: -0.5px;
    }

    .welcome-banner p {
        font-size: 1.05rem;
        opacity: 0.85;
        margin-bottom: 2rem;
        max-width: 600px;
        line-height: 1.5;
    }

    .quick-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .quick-action-btn {
        padding: 0.8rem 1.8rem;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 30px;
        color: white;
        font-weight: 600;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        text-decoration: none;
    }

    .quick-action-btn:hover {
        background: white;
        color: var(--primary);
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }
    
    .quick-action-btn:hover i {
        color: var(--accent);
    }

    .section-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--primary);
        margin: 2.5rem 0 1.5rem 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        letter-spacing: -0.5px;
    }

    .section-title a {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--gray-500);
        transition: var(--transition);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        background: var(--gray-100);
    }
    
    .section-title a:hover {
        background: var(--gray-200);
        color: var(--primary);
    }

    /* Recent Bookings List Enhancement */
    .booking-card {
        display: flex;
        gap: 1.5rem;
        padding: 1.2rem;
        background: white;
        border: 1px solid var(--gray-100);
        border-radius: 20px;
        margin-bottom: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        align-items: center;
    }

    .booking-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
        border-color: var(--gray-200);
    }

    .booking-car-image {
        width: 120px;
        height: 85px;
        background: var(--gray-50);
        border-radius: 14px;
        overflow: hidden;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--gray-100);
    }

    .booking-car-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .booking-car-image i {
        font-size: 2.5rem;
        color: var(--gray-300);
    }

    .booking-info {
        flex: 1;
    }

    .booking-info h4 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.2rem;
    }

    .booking-info p {
        font-size: 0.9rem;
        color: var(--gray-500);
        font-weight: 500;
        margin-bottom: 0.4rem;
    }
    
    .booking-info .date-text {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: var(--gray-600);
        background: var(--gray-50);
        padding: 0.3rem 0.8rem;
        border-radius: 8px;
    }

    .booking-meta {
        text-align: right;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.5rem;
    }

    .booking-price {
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--primary);
    }

    /* Recommended Cars Grid */
    .car-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .car-card-mini {
        background: white;
        border: 1px solid var(--gray-100);
        border-radius: 20px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--shadow-sm);
    }

    .car-card-mini:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-xl);
        border-color: var(--gray-200);
    }

    .car-card-mini-image {
        height: 160px;
        background: var(--gray-50);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

    .car-card-mini-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    
    .car-card-mini:hover .car-card-mini-image img {
        transform: scale(1.08);
    }

    .car-card-mini-image i {
        font-size: 3.5rem;
        color: var(--gray-300);
    }

    .car-card-mini-content {
        padding: 1.2rem;
    }

    .car-card-mini-content h4 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.2rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .car-card-mini-content p {
        font-size: 0.9rem;
        color: var(--gray-500);
        font-weight: 500;
        margin-bottom: 1.2rem;
    }

    .car-card-mini-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px dashed var(--gray-200);
    }

    .car-price-mini {
        font-weight: 800;
        font-size: 1.1rem;
        color: var(--primary);
    }

    .car-price-mini span {
        font-weight: 600;
        font-size: 0.8rem;
        color: var(--gray-400);
    }

    .book-btn-mini {
        padding: 0.6rem 1.2rem;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 20px; /* Pill shape */
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
    }

    .book-btn-mini:hover {
        background: var(--accent); /* Red hover */
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 59, 59, 0.3);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: var(--gray-50);
        border-radius: 20px;
        border: 2px dashed var(--gray-200);
    }

    .empty-state i {
        font-size: 3.5rem;
        color: var(--gray-300);
        margin-bottom: 1.2rem;
    }
    
    .empty-state h4 {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: var(--gray-500);
    }
    
    @media (max-width: 768px) {
        .welcome-banner { padding: 2rem 1.5rem; }
        .welcome-banner h2 { font-size: 1.8rem; }
        .booking-card { flex-direction: column; align-items: flex-start; gap: 1rem; }
        .booking-car-image { width: 100%; height: 160px; }
        .booking-meta { text-align: left; align-items: flex-start; flex-direction: row; justify-content: space-between; width: 100%; }
    }
</style>

<!-- Welcome Banner -->
<div class="welcome-banner">
    <h2>Welcome back, <?php echo htmlspecialchars($userName); ?>! 👋</h2>
    <p>Ready for your next adventure? Browse our collection of premium vehicles and manage your trips easily.</p>
    <div class="quick-actions">
        <a href="<?php echo $base_url; ?>user/cars.php" class="quick-action-btn">
            <i class="fas fa-car-side"></i> Browse Cars
        </a>
        <a onclick="loadPage('bookings')" class="quick-action-btn">
            <i class="fas fa-calendar-alt"></i> My Bookings
        </a>
        <a onclick="loadPage('profile')" class="quick-action-btn">
            <i class="fas fa-user-circle"></i> Profile
        </a>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: var(--info);">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-value"><?php echo $total_bookings; ?></div>
        <div class="stat-label">Total Bookings</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-value"><?php echo $active_bookings; ?></div>
        <div class="stat-label">Active Bookings</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-value"><?php echo $completed_trips; ?></div>
        <div class="stat-label">Completed Trips</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-value">$<?php echo number_format($total_spent, 0); ?></div>
        <div class="stat-label">Total Spent</div>
    </div>
</div>

<!-- Recent Bookings -->
<div class="card" style="border-radius: 20px; border-color: var(--gray-100);">
    <div class="card-header" style="background: transparent; border-bottom: 1px dashed var(--gray-200); padding: 1.5rem;">
        <h3 class="card-title" style="font-size: 1.2rem;">Recent Bookings</h3>
        <a onclick="loadPage('bookings')" style="cursor: pointer; color: var(--gray-500); font-weight: 600; font-size: 0.9rem; padding: 0.5rem 1rem; border-radius: 20px; background: var(--gray-50); transition: all 0.3s ease;" onmouseover="this.style.background='var(--gray-200)'; this.style.color='var(--primary)';" onmouseout="this.style.background='var(--gray-50)'; this.style.color='var(--gray-500)';">View All</a>
    </div>
    <div class="card-body" style="padding: 1.5rem;">
        <?php if (mysqli_num_rows($recent_bookings) > 0): ?>
            <?php while ($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                <div class="booking-card">
                    <div class="booking-car-image">
                        <?php if (!empty($booking['image'])): ?>
                            <img src="<?php echo $base_url; ?>uploads/car_images/<?php echo htmlspecialchars($booking['image']); ?>" alt="">
                        <?php else: ?>
                            <i class="fas fa-car"></i>
                        <?php endif; ?>
                    </div>
                    <div class="booking-info">
                        <h4><?php echo htmlspecialchars($booking['car_name'] ?? 'N/A'); ?></h4>
                        <p><?php echo htmlspecialchars($booking['brand'] ?? ''); ?></p>
                        <span class="date-text">
                            <i class="fas fa-calendar" style="color: var(--gray-400);"></i> 
                            <?php echo date('M d', strtotime($booking['pickup_date'])); ?> - 
                            <?php echo date('M d, Y', strtotime($booking['return_date'])); ?>
                        </span>
                    </div>
                    <div class="booking-meta">
                        <div class="booking-price">$<?php echo number_format($booking['total_price'], 0); ?></div>
                        <span class="status-badge <?php echo strtolower($booking['booking_status']); ?>">
                            <?php echo $booking['booking_status']; ?>
                        </span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-xmark"></i>
                <h4>No bookings yet</h4>
                <p>Start your journey by browsing our exclusive car collection!</p>
                <a href="<?php echo $base_url; ?>user/cars.php" class="btn btn-primary" style="margin-top: 1.5rem;">
                    <i class="fas fa-car-side"></i> Browse Cars
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recommended Cars -->
<div class="section-title">
    <span>Recommended for You</span>
    <a href="<?php echo $base_url; ?>user/cars.php">View All Cars</a>
</div>

<div class="car-card-grid">
    <?php while ($car = mysqli_fetch_assoc($recommended_cars)): ?>
        <div class="car-card-mini">
            <div class="car-card-mini-image">
                <?php if (!empty($car['image'])): ?>
                    <img src="<?php echo $base_url; ?>uploads/car_images/<?php echo htmlspecialchars($car['image']); ?>" alt="">
                <?php else: ?>
                    <i class="fas fa-car"></i>
                <?php endif; ?>
            </div>
            <div class="car-card-mini-content">
                <h4><?php echo htmlspecialchars($car['car_name']); ?></h4>
                <p><?php echo htmlspecialchars($car['brand']); ?> · <?php echo $car['year']; ?></p>
                <div class="car-card-mini-footer">
                    <span class="car-price-mini">$<?php echo number_format($car['price_per_day'], 0); ?><span>/day</span></span>
                    <a href="<?php echo $base_url; ?>user/car_details.php?id=<?php echo $car['id']; ?>" class="book-btn-mini">Book Now</a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>