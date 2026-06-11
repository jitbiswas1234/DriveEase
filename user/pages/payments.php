<?php
// Note: Ensure $user_id is defined in your parent file session
$user_id = $_SESSION['user_id'];

$payments = mysqli_query($conn, "
    SELECT b.id, b.booking_code, b.total_price, b.payment_status, b.payment_id, b.created_at, c.car_name
    FROM bookings b
    LEFT JOIN cars c ON b.car_id = c.id
    WHERE b.user_id = '$user_id' AND b.payment_status = 'Paid'
    ORDER BY b.created_at DESC
");
?>

<style>
    .payment-container {
        padding: 0;
    }

    .payment-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--gray-200);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    .card-header {
        padding: 1.8rem 2rem;
        border-bottom: 1px solid var(--gray-100);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary);
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .data-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: left;
    }

    .data-table th { 
        padding: 1.2rem 1.5rem; 
        color: var(--gray-500); 
        font-size: 0.8rem; 
        text-transform: uppercase; 
        letter-spacing: 0.05em;
        background: var(--gray-50);
        border-bottom: 1px solid var(--gray-200);
    }

    .data-table td { 
        padding: 1.2rem 1.5rem; 
        border-bottom: 1px solid var(--gray-100); 
        font-size: 0.95rem; 
        color: var(--gray-700);
    }

    .data-table tr:hover {
        background: var(--gray-50);
    }

    .status-paid { 
        background: rgba(220, 252, 231, 0.5); 
        color: #166534; 
        padding: 0.4rem 0.8rem; 
        border-radius: 30px; 
        font-weight: 600; 
        font-size: 0.8rem;
        border: 1px solid rgba(22, 101, 52, 0.1);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--gray-500);
    }
</style>

<div class="payment-container">
    <div class="payment-card">
        <div class="card-header">
            <h2 class="card-title">Payment History</h2>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Car Model</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($payments) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($payments)): ?>
                        <tr>
                            <td style="font-family: monospace; color: var(--gray-500);">#<?= htmlspecialchars($row['payment_id'] ?? 'N/A') ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($row['car_name']) ?></td>
                            <td style="font-weight: 800; color: var(--primary);">₹<?= number_format($row['total_price'], 2) ?></td>
                            <td><span style="color: var(--gray-600);">Razorpay</span></td>
                            <td><span class="status-paid"><?= htmlspecialchars($row['payment_status']) ?></span></td>
                            <td style="color: var(--gray-500);"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-receipt" style="font-size: 2rem; margin-bottom: 1rem; color: var(--gray-300);"></i>
                                <p>No payment history found.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>