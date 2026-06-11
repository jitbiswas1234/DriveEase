<?php
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where = "WHERE b.user_id = $user_id";
if ($status_filter && $status_filter !== 'all') {
    $where .= " AND b.booking_status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}

$bookings = mysqli_query($conn, "
    SELECT b.*, c.car_name, c.brand, c.image, c.car_type 
    FROM bookings b 
    LEFT JOIN cars c ON b.car_id = c.id 
    $where
    ORDER BY b.created_at DESC
");

// Count by status
$all_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE user_id = $user_id"))['count'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE user_id = $user_id AND booking_status = 'Pending'"))['count'];
$confirmed_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE user_id = $user_id AND booking_status = 'Confirmed'"))['count'];
$completed_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE user_id = $user_id AND booking_status = 'Completed'"))['count'];
?>

<style>
    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 0.7rem 1.2rem;
        background: var(--gray-100);
        border: none;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--gray-600);
        cursor: pointer;
        transition: var(--transition);
    }

    .filter-tab:hover {
        background: var(--gray-200);
    }

    .filter-tab.active {
        background: var(--primary);
        color: white;
    }

    .filter-tab .count {
        background: rgba(0,0,0,0.1);
        padding: 0.2rem 0.5rem;
        border-radius: 50px;
        font-size: 0.75rem;
        margin-left: 0.3rem;
    }

    .filter-tab.active .count {
        background: rgba(255,255,255,0.2);
    }

    .booking-list-card {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: var(--transition);
    }

    .booking-list-card:hover {
        box-shadow: var(--shadow-lg);
    }

    .booking-list-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .booking-id {
        font-size: 0.85rem;
        color: var(--gray-500);
        margin-bottom: 0.3rem;
    }

    .booking-list-content {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 1.5rem;
        align-items: center;
    }

    .booking-car-img {
        width: 120px;
        height: 90px;
        background: var(--gray-100);
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .booking-car-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .booking-car-img i {
        font-size: 2.5rem;
        color: var(--gray-300);
    }

    .booking-details h3 {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 0.3rem;
    }

    .booking-details p {
        font-size: 0.9rem;
        color: var(--gray-500);
        margin-bottom: 0.5rem;
    }

    .booking-dates {
        display: flex;
        gap: 2rem;
        margin-top: 0.5rem;
    }

    .date-item {
        display: flex;
        flex-direction: column;
    }

    .date-item label {
        font-size: 0.75rem;
        color: var(--gray-400);
        text-transform: uppercase;
    }

    .date-item span {
        font-weight: 600;
        color: var(--primary);
    }

    .booking-amount {
        text-align: right;
    }

    .booking-amount .price {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary);
    }

    .booking-amount .days {
        font-size: 0.85rem;
        color: var(--gray-500);
    }

    .booking-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--gray-200);
    }

    .booking-actions .btn {
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
    }

    @media (max-width: 768px) {
        .booking-list-content {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .booking-car-img {
            width: 100%;
            height: 150px;
        }

        .booking-amount {
            text-align: center;
        }

        .booking-dates {
            justify-content: center;
        }
    }
</style>

<!-- Filter Tabs -->
<div class="filter-tabs">
    <button class="filter-tab <?php echo !$status_filter || $status_filter == 'all' ? 'active' : ''; ?>" onclick="filterBookings('all')">
        All <span class="count"><?php echo $all_count; ?></span>
    </button>
    <button class="filter-tab <?php echo $status_filter == 'Pending' ? 'active' : ''; ?>" onclick="filterBookings('Pending')">
        Pending <span class="count"><?php echo $pending_count; ?></span>
    </button>
    <button class="filter-tab <?php echo $status_filter == 'Confirmed' ? 'active' : ''; ?>" onclick="filterBookings('Confirmed')">
        Confirmed <span class="count"><?php echo $confirmed_count; ?></span>
    </button>
    <button class="filter-tab <?php echo $status_filter == 'Completed' ? 'active' : ''; ?>" onclick="filterBookings('Completed')">
        Completed <span class="count"><?php echo $completed_count; ?></span>
    </button>
</div>

<!-- Bookings List -->
<?php if (mysqli_num_rows($bookings) > 0): ?>
    <?php while ($booking = mysqli_fetch_assoc($bookings)): ?>
        <div class="booking-list-card">
            <div class="booking-list-header">
                <div>
                    <div class="booking-id">Booking #<?php echo htmlspecialchars($booking['booking_code'] ?? 'BK' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT)); ?></div>
                    <small style="color: var(--gray-400);">Booked on <?php echo date('M d, Y', strtotime($booking['created_at'])); ?></small>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <span class="status-badge <?php echo strtolower($booking['booking_status']); ?>">
                        <?php echo $booking['booking_status']; ?>
                    </span>
                    <span class="status-badge <?php echo strtolower($booking['payment_status']); ?>">
                        <?php echo $booking['payment_status']; ?>
                    </span>
                </div>
            </div>

            <div class="booking-list-content">
                <div class="booking-car-img">
                    <?php if (!empty($booking['image'])): ?>
                        <img src="<?php echo $base_url; ?>uploads/car_images/<?php echo htmlspecialchars($booking['image']); ?>" alt="">
                    <?php else: ?>
                        <i class="fas fa-car"></i>
                    <?php endif; ?>
                </div>

                <div class="booking-details">
                    <h3><?php echo htmlspecialchars($booking['car_name'] ?? 'N/A'); ?></h3>
                    <p><?php echo htmlspecialchars($booking['brand'] ?? ''); ?> · <?php echo htmlspecialchars($booking['car_type'] ?? ''); ?></p>
                    <div class="booking-dates">
                        <div class="date-item">
                            <label>Pick-up</label>
                            <span><?php echo date('M d, Y', strtotime($booking['pickup_date'])); ?></span>
                        </div>
                        <div class="date-item">
                            <label>Return</label>
                            <span><?php echo date('M d, Y', strtotime($booking['return_date'])); ?></span>
                        </div>
                    </div>
                </div>

                <div class="booking-amount">
                    <div class="price">$<?php echo number_format($booking['total_price'], 2); ?></div>
                    <div class="days"><?php echo $booking['total_days']; ?> days</div>
                </div>
            </div>

            <div class="booking-actions">
                <a href="<?php echo $base_url; ?>user/booking_details.php?id=<?php echo $booking['id']; ?>" class="btn btn-outline">
                    <i class="fas fa-eye"></i> View Details
                </a>
                <?php if ($booking['booking_status'] == 'Pending' && $booking['payment_status'] != 'Paid'): ?>
                    <a href="<?php echo $base_url; ?>booking/payment.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> Pay Now
                    </a>
                    <button class="btn" style="background: var(--danger); color: white;" onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 4rem;">
            <i class="fas fa-calendar-xmark" style="font-size: 4rem; color: var(--gray-300); margin-bottom: 1rem;"></i>
            <h3 style="margin-bottom: 0.5rem; color: var(--gray-600);">No bookings found</h3>
            <p style="color: var(--gray-500); margin-bottom: 1.5rem;">You haven't made any bookings yet.</p>
            <a href="<?php echo $base_url; ?>user/cars.php" class="btn btn-primary">
                <i class="fas fa-car"></i> Browse Cars
            </a>
        </div>
    </div>
<?php endif; ?>

<script>
function filterBookings(status) {
    const url = new URL(window.location);
    url.searchParams.set('page', 'bookings');
    url.searchParams.set('status', status);
    window.location = url;
}

function cancelBooking(id) {
    if (confirm('Are you sure you want to cancel this booking?')) {
        fetch('<?php echo $base_url; ?>user/api/cancel_booking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'booking_id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to cancel booking');
            }
        });
    }
}
</script>