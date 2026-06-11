<?php
// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where_conditions = [];
$params = [];
$types = '';

if ($status_filter && $status_filter !== 'all') {
    $where_conditions[] = "b.booking_status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if ($search) {
    $where_conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR c.car_name LIKE ? OR b.booking_code LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= 'ssss';
}

$where_clause = count($where_conditions) > 0 ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count bookings by status
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'Pending'"))['count'];
$confirmed_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'Confirmed'"))['count'];
$completed_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'Completed'"))['count'];
$cancelled_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'Cancelled'"))['count'];
$total_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'];

// Fetch bookings
$query = "
    SELECT b.*, 
           u.name as customer_name, 
           u.email as customer_email,
           u.phone as customer_phone,
           c.car_name, 
           c.brand,
           c.image as car_image
    FROM bookings b 
    LEFT JOIN users u ON b.user_id = u.id 
    LEFT JOIN cars c ON b.car_id = c.id 
    $where_clause
    ORDER BY b.created_at DESC
";

if (count($params) > 0) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $bookings = $stmt->get_result();
} else {
    $bookings = mysqli_query($conn, $query);
}
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
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--gray-600);
        cursor: pointer;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.5rem;
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
    }

    .filter-tab.active .count {
        background: rgba(255,255,255,0.2);
    }

    .search-bar {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .search-input {
        flex: 1;
        min-width: 300px;
        padding: 0.8rem 1rem;
        padding-left: 2.5rem;
        border: 1px solid var(--gray-200);
        border-radius: 10px;
        font-size: 0.95rem;
        transition: var(--transition);
        background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23a3a3a3' viewBox='0 0 24 24'%3E%3Cpath d='M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z'/%3E%3C/svg%3E") no-repeat 0.8rem center;
        background-size: 20px;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
    }

    .booking-card {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: var(--transition);
    }

    .booking-card:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .booking-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .booking-id {
        font-weight: 700;
        color: var(--primary);
        font-size: 1.1rem;
    }

    .booking-date {
        color: var(--gray-500);
        font-size: 0.85rem;
    }

    .booking-content {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1.5rem;
    }

    .booking-info h4 {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--gray-400);
        margin-bottom: 0.5rem;
        letter-spacing: 0.5px;
    }

    .booking-info p {
        font-weight: 500;
        color: var(--gray-700);
    }

    .booking-info .sub {
        font-size: 0.85rem;
        color: var(--gray-500);
        font-weight: 400;
    }

    .booking-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--gray-200);
    }

    .booking-actions .btn {
        padding: 0.5rem 1rem;
        font-size: 0.85rem;
    }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal {
        background: white;
        border-radius: 16px;
        width: 100%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
        margin: 1rem;
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        font-size: 1.2rem;
        font-weight: 700;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--gray-500);
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        padding: 1.5rem;
        border-top: 1px solid var(--gray-200);
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
    }

    .form-select {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        font-size: 0.95rem;
        background: white;
        cursor: pointer;
    }

    .form-select:focus {
        outline: none;
        border-color: var(--primary);
    }

    @media (max-width: 768px) {
        .booking-content {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Filter Tabs -->
<div class="filter-tabs">
    <button class="filter-tab <?php echo $status_filter == '' || $status_filter == 'all' ? 'active' : ''; ?>" onclick="filterBookings('all')">
        All <span class="count"><?php echo $total_count; ?></span>
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
    <button class="filter-tab <?php echo $status_filter == 'Cancelled' ? 'active' : ''; ?>" onclick="filterBookings('Cancelled')">
        Cancelled <span class="count"><?php echo $cancelled_count; ?></span>
    </button>
</div>

<!-- Search Bar -->
<div class="search-bar">
    <input type="text" class="search-input" id="searchInput" placeholder="Search by customer, car, or booking ID..." value="<?php echo htmlspecialchars($search); ?>">
    <button class="btn btn-outline" onclick="exportBookings()">
        <i class="fas fa-download"></i> Export
    </button>
</div>

<!-- Bookings List -->
<div id="bookingsList">
    <?php if (mysqli_num_rows($bookings) > 0): ?>
        <?php while ($booking = mysqli_fetch_assoc($bookings)): ?>
            <div class="booking-card">
                <div class="booking-header">
                    <div>
                        <div class="booking-id">#<?php echo htmlspecialchars($booking['booking_code'] ?? 'BK' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT)); ?></div>
                        <div class="booking-date">Booked on <?php echo date('M d, Y h:i A', strtotime($booking['created_at'])); ?></div>
                    </div>
                    <span class="status-badge <?php echo strtolower($booking['booking_status']); ?>">
                        <?php echo $booking['booking_status']; ?>
                    </span>
                </div>

                <div class="booking-content">
                    <div class="booking-info">
                        <h4>Customer</h4>
                        <p><?php echo htmlspecialchars($booking['customer_name'] ?? 'N/A'); ?></p>
                        <p class="sub"><?php echo htmlspecialchars($booking['customer_email'] ?? ''); ?></p>
                        <p class="sub"><?php echo htmlspecialchars($booking['customer_phone'] ?? ''); ?></p>
                    </div>

                    <div class="booking-info">
                        <h4>Vehicle</h4>
                        <p><?php echo htmlspecialchars($booking['car_name'] ?? 'N/A'); ?></p>
                        <p class="sub"><?php echo htmlspecialchars($booking['brand'] ?? ''); ?></p>
                    </div>

                    <div class="booking-info">
                        <h4>Rental Period</h4>
                        <p><?php echo date('M d', strtotime($booking['pickup_date'])); ?> - <?php echo date('M d, Y', strtotime($booking['return_date'])); ?></p>
                        <p class="sub"><?php echo $booking['total_days']; ?> days</p>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                    <div>
                        <span style="font-size: 0.85rem; color: var(--gray-500);">Total Amount:</span>
                        <span style="font-size: 1.3rem; font-weight: 800; color: var(--primary); margin-left: 0.5rem;">
                            $<?php echo number_format($booking['total_price'], 2); ?>
                        </span>
                        <span class="status-badge <?php echo strtolower($booking['payment_status']); ?>" style="margin-left: 0.5rem;">
                            <?php echo $booking['payment_status']; ?>
                        </span>
                    </div>
                </div>

                <div class="booking-actions">
                    <!-- <button class="btn btn-outline" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                        <i class="fas fa-eye"></i> View
                    </button> -->
                    <?php if ($booking['booking_status'] == 'Pending'): ?>
                        <button class="btn btn-primary" onclick="updateStatus(<?php echo $booking['id']; ?>, 'Confirmed')">
                            <i class="fas fa-check"></i> Confirm
                        </button>
                        <button class="btn" style="background: var(--danger); color: white;" onclick="updateStatus(<?php echo $booking['id']; ?>, 'Cancelled')">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    <?php elseif ($booking['booking_status'] == 'Confirmed'): ?>
                        <button class="btn btn-primary" onclick="updateStatus(<?php echo $booking['id']; ?>, 'Completed')">
                            <i class="fas fa-check-double"></i> Complete
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 4rem; background: white; border-radius: 12px; border: 1px solid var(--gray-200);">
            <i class="fas fa-calendar-xmark" style="font-size: 3rem; color: var(--gray-300); margin-bottom: 1rem;"></i>
            <h3 style="color: var(--gray-500); margin-bottom: 0.5rem;">No Bookings Found</h3>
            <p style="color: var(--gray-400);">There are no bookings matching your criteria.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Status Update Modal -->
<div class="modal-overlay" id="statusModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Update Booking Status</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="statusForm" method="POST" action="api/update_booking.php">
            <div class="modal-body">
                <input type="hidden" name="booking_id" id="modalBookingId">
                <div class="form-group">
                    <label>New Status</label>
                    <select name="status" id="modalStatus" class="form-select">
                        <option value="Pending">Pending</option>
                        <option value="Confirmed">Confirmed</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Status</label>
                    <select name="payment_status" id="modalPaymentStatus" class="form-select">
                        <option value="Pending">Pending</option>
                        <option value="Paid">Paid</option>
                        <option value="Refunded">Refunded</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterBookings(status) {
    const url = new URL(window.location);
    url.searchParams.set('status', status);
    window.location = url;
}

document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const url = new URL(window.location);
        url.searchParams.set('search', this.value);
        window.location = url;
    }
});

function updateStatus(bookingId, status) {
    if (confirm('Are you sure you want to update this booking to ' + status + '?')) {
        fetch('api/update_booking.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'booking_id=' + bookingId + '&status=' + status
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update booking');
            }
        });
    }
}

function viewBooking(id) {
    window.location.href = 'view_booking.php?id=' + id;
}

function closeModal() {
    document.getElementById('statusModal').classList.remove('active');
}

function exportBookings() {
    window.location.href = 'api/export_bookings.php';
}
</script>