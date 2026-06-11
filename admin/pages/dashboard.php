<?php
// Fetch stats
$stats = [];
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM cars");
$stats['total_cars'] = mysqli_fetch_assoc($result)['count'];

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM cars WHERE status = 'Available'");
$stats['available_cars'] = mysqli_fetch_assoc($result)['count'];

$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'Pending'");
$stats['pending_bookings'] = mysqli_fetch_assoc($result)['count'];

$result = mysqli_query($conn, "SELECT SUM(total_price) as revenue FROM bookings WHERE payment_status = 'Paid' AND MONTH(created_at) = MONTH(CURRENT_DATE())");
$stats['monthly_revenue'] = mysqli_fetch_assoc($result)['revenue'] ?? 0;

// Recent bookings
$recentBookings = mysqli_query($conn, "
    SELECT b.*, u.name as customer_name, c.car_name 
    FROM bookings b 
    LEFT JOIN users u ON b.user_id = u.id 
    LEFT JOIN cars c ON b.car_id = c.id 
    ORDER BY b.created_at DESC LIMIT 5
");
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon"><i class="fas fa-car"></i></div>
        </div>
        <div class="stat-value"><?php echo $stats['total_cars']; ?></div>
        <div class="stat-label">Total Cars</div>
        <div style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--success);">
            <?php echo $stats['available_cars']; ?> available
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                <i class="fas fa-calendar-clock"></i>
            </div>
        </div>
        <div class="stat-value"><?php echo $stats['pending_bookings']; ?></div>
        <div class="stat-label">Pending Bookings</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: var(--success);">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
        <div class="stat-value">$<?php echo number_format($stats['monthly_revenue'], 0); ?></div>
        <div class="stat-label">This Month</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: var(--info);">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-value">1,234</div>
        <div class="stat-label">Total Customers</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Bookings</h3>
        <a onclick="loadPage('bookings')" style="color: var(--primary); font-weight: 600; cursor: pointer;">View All</a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Car</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($booking = mysqli_fetch_assoc($recentBookings)): ?>
                <tr>
                    <td>#<?php echo $booking['id']; ?></td>
                    <td><?php echo htmlspecialchars($booking['customer_name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($booking['car_name'] ?? 'N/A'); ?></td>
                    <td><?php echo date('M d, Y', strtotime($booking['pickup_date'])); ?></td>
                    <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                    <td><span class="status-badge <?php echo strtolower($booking['booking_status']); ?>"><?php echo $booking['booking_status']; ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function initDashboardCharts() {
    // Initialize charts here if needed
    console.log('Dashboard initialized');
}
</script>