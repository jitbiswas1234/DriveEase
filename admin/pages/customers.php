<?php
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = '';
if ($search) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $where = "WHERE name LIKE '%$search_safe%' OR email LIKE '%$search_safe%' OR phone LIKE '%$search_safe%'";
}

$customers = mysqli_query($conn, "
    SELECT u.*, 
           COUNT(b.id) as total_bookings,
           SUM(CASE WHEN b.booking_status = 'Completed' THEN b.total_price ELSE 0 END) as total_spent
    FROM users u
    LEFT JOIN bookings b ON u.id = b.user_id
    $where
    GROUP BY u.id
    ORDER BY u.created_at DESC
");

$total_customers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
?>

<style>
    .customers-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .customers-stats {
        display: flex;
        gap: 1rem;
    }

    .mini-stat {
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        border: 1px solid var(--gray-200);
    }

    .mini-stat-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary);
    }

    .mini-stat-label {
        font-size: 0.8rem;
        color: var(--gray-500);
    }

    .customer-row {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .customer-avatar {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, var(--primary), var(--gray-700));
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
    }

    .customer-info h4 {
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 0.2rem;
    }

    .customer-info p {
        font-size: 0.85rem;
        color: var(--gray-500);
    }
</style>

<div class="customers-header">
    <div class="customers-stats">
        <div class="mini-stat">
            <div class="mini-stat-value"><?php echo $total_customers; ?></div>
            <div class="mini-stat-label">Total Customers</div>
        </div>
    </div>

    <div style="display: flex; gap: 1rem;">
        <input type="text" class="search-input" id="searchInput" placeholder="Search customers..." value="<?php echo htmlspecialchars($search); ?>" style="width: 300px;">
        <button class="btn btn-outline" onclick="exportCustomers()">
            <i class="fas fa-download"></i> Export
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>License</th>
                    <th>Bookings</th>
                    <th>Total Spent</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($customers) > 0): ?>
                    <?php while ($customer = mysqli_fetch_assoc($customers)): ?>
                        <tr>
                            <td>
                                <div class="customer-row">
                                    <div class="customer-avatar">
                                        <?php echo strtoupper(substr($customer['name'], 0, 1)); ?>
                                    </div>
                                    <div class="customer-info">
                                        <h4><?php echo htmlspecialchars($customer['name']); ?></h4>
                                        <p><?php echo htmlspecialchars($customer['email']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($customer['license_number'] ?? 'N/A'); ?></td>
                            <td>
                                <span style="font-weight: 600;"><?php echo $customer['total_bookings']; ?></span>
                            </td>
                            <td>
                                <span style="font-weight: 600;">$<?php echo number_format($customer['total_spent'] ?? 0, 2); ?></span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                            <td>
                                <button class="action-btn" title="View" onclick="viewCustomer(<?php echo $customer['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn" title="Email" onclick="emailCustomer('<?php echo $customer['email']; ?>')">
                                    <i class="fas fa-envelope"></i>
                                </button>
                                <button class="action-btn" title="Delete" style="color: var(--danger);" onclick="deleteCustomer(<?php echo $customer['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--gray-500);">
                            <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                            No customers found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const url = new URL(window.location);
        url.searchParams.set('search', this.value);
        window.location = url;
    }
});

function viewCustomer(id) {
    window.location.href = 'view_customer.php?id=' + id;
}

function emailCustomer(email) {
    window.location.href = 'mailto:' + email;
}

function deleteCustomer(id) {
    if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
        fetch('api/delete_customer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to delete customer');
            }
        });
    }
}

function exportCustomers() {
    window.location.href = 'api/export_customers.php';
}
</script>