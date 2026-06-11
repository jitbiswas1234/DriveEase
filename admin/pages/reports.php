<?php
// Monthly revenue data
$monthly_revenue = mysqli_query($conn, "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        SUM(CASE WHEN payment_status = 'Paid' THEN total_price ELSE 0 END) as revenue,
        COUNT(*) as bookings
    FROM bookings
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");

$monthly_data = [];
while ($row = mysqli_fetch_assoc($monthly_revenue)) {
    $monthly_data[] = $row;
}

// Car type distribution
$car_types = mysqli_query($conn, "
    SELECT car_type, COUNT(*) as count
    FROM cars
    GROUP BY car_type
    ORDER BY count DESC
");

$car_type_data = [];
while ($row = mysqli_fetch_assoc($car_types)) {
    $car_type_data[] = $row;
}

// Top cars
$top_cars = mysqli_query($conn, "
    SELECT c.car_name, c.brand, COUNT(b.id) as bookings, SUM(b.total_price) as revenue
    FROM cars c
    LEFT JOIN bookings b ON c.id = b.car_id AND b.payment_status = 'Paid'
    GROUP BY c.id
    ORDER BY revenue DESC
    LIMIT 5
");
?>

<style>
    .reports-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .chart-card {
        background: white;
        border-radius: 16px;
        border: 1px solid var(--gray-200);
        padding: 1.5rem;
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: var(--primary);
    }

    .chart-container {
        position: relative;
        height: 300px;
    }

    .summary-cards {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .summary-card {
        background: white;
        border-radius: 12px;
        border: 1px solid var(--gray-200);
        padding: 1.5rem;
        text-align: center;
    }

    .summary-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.3rem;
    }

    .summary-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary);
        margin-bottom: 0.3rem;
    }

    .summary-label {
        font-size: 0.85rem;
        color: var(--gray-500);
    }

    .top-list {
        list-style: none;
    }

    .top-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid var(--gray-100);
    }

    .top-list li:last-child {
        border-bottom: none;
    }

    .rank-badge {
        width: 28px;
        height: 28px;
        background: var(--gray-100);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        margin-right: 1rem;
    }

    .rank-badge.gold { background: #fef3c7; color: #b45309; }
    .rank-badge.silver { background: #e5e7eb; color: #374151; }
    .rank-badge.bronze { background: #fed7aa; color: #c2410c; }

    @media (max-width: 1024px) {
        .reports-grid {
            grid-template-columns: 1fr;
        }

        .summary-cards {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Summary Cards -->
<div class="summary-cards">
    <?php
    $total_rev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_price) as total FROM bookings WHERE payment_status = 'Paid'"))['total'] ?? 0;
    $total_bookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings"))['count'];
    $avg_booking = $total_bookings > 0 ? $total_rev / $total_bookings : 0;
    $active_cars = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cars WHERE status = 'Available'"))['count'];
    ?>
    <div class="summary-card">
        <div class="summary-icon" style="background: rgba(34, 197, 94, 0.1); color: var(--success);">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="summary-value">$<?php echo number_format($total_rev, 0); ?></div>
        <div class="summary-label">Total Revenue</div>
    </div>
    <div class="summary-card">
        <div class="summary-icon" style="background: rgba(59, 130, 246, 0.1); color: var(--info);">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="summary-value"><?php echo $total_bookings; ?></div>
        <div class="summary-label">Total Bookings</div>
    </div>
    <div class="summary-card">
        <div class="summary-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="summary-value">$<?php echo number_format($avg_booking, 0); ?></div>
        <div class="summary-label">Avg. Booking Value</div>
    </div>
    <div class="summary-card">
        <div class="summary-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
            <i class="fas fa-car"></i>
        </div>
        <div class="summary-value"><?php echo $active_cars; ?></div>
        <div class="summary-label">Active Cars</div>
    </div>
</div>

<div class="reports-grid">
    <!-- Revenue Chart -->
    <div class="chart-card">
        <h3 class="chart-title">Revenue Overview (Last 12 Months)</h3>
        <div class="chart-container">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Car Types Distribution -->
    <div class="chart-card">
        <h3 class="chart-title">Fleet Distribution</h3>
        <div class="chart-container">
            <canvas id="carTypesChart"></canvas>
        </div>
    </div>
</div>

<div class="reports-grid">
    <!-- Top Performing Cars -->
    <div class="chart-card">
        <h3 class="chart-title">Top Performing Cars</h3>
        <ul class="top-list">
            <?php $rank = 1; ?>
            <?php mysqli_data_seek($top_cars, 0); ?>
            <?php while ($car = mysqli_fetch_assoc($top_cars)): ?>
                <li>
                    <div style="display: flex; align-items: center;">
                        <span class="rank-badge <?php echo $rank == 1 ? 'gold' : ($rank == 2 ? 'silver' : ($rank == 3 ? 'bronze' : '')); ?>">
                            <?php echo $rank; ?>
                        </span>
                        <div>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($car['car_name']); ?></div>
                            <div style="font-size: 0.85rem; color: var(--gray-500);"><?php echo htmlspecialchars($car['brand']); ?></div>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 700; color: var(--success);">$<?php echo number_format($car['revenue'] ?? 0, 0); ?></div>
                        <div style="font-size: 0.85rem; color: var(--gray-500);"><?php echo $car['bookings']; ?> bookings</div>
                    </div>
                </li>
                <?php $rank++; ?>
            <?php endwhile; ?>
        </ul>
    </div>

    <!-- Export Options -->
    <div class="chart-card">
        <h3 class="chart-title">Export Reports</h3>
        <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
            <button class="btn btn-outline" style="width: 100%;" onclick="exportReport('revenue')">
                <i class="fas fa-file-excel"></i> Revenue Report (Excel)
            </button>
            <button class="btn btn-outline" style="width: 100%;" onclick="exportReport('bookings')">
                <i class="fas fa-file-pdf"></i> Bookings Report (PDF)
            </button>
            <button class="btn btn-outline" style="width: 100%;" onclick="exportReport('customers')">
                <i class="fas fa-file-csv"></i> Customer Report (CSV)
            </button>
            <button class="btn btn-primary" style="width: 100%;" onclick="generateFullReport()">
                <i class="fas fa-download"></i> Full Report
            </button>
        </div>
    </div>
</div>

<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart');
const monthlyData = <?php echo json_encode($monthly_data); ?>;

new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: monthlyData.map(d => d.month),
        datasets: [{
            label: 'Revenue',
            data: monthlyData.map(d => d.revenue),
            borderColor: '#000',
            backgroundColor: 'rgba(0,0,0,0.05)',
            fill: true,
            tension: 0.4,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});

// Car Types Chart
const carTypesCtx = document.getElementById('carTypesChart');
const carTypeData = <?php echo json_encode($car_type_data); ?>;

new Chart(carTypesCtx, {
    type: 'doughnut',
    data: {
        labels: carTypeData.map(d => d.car_type),
        datasets: [{
            data: carTypeData.map(d => d.count),
            backgroundColor: ['#000', '#333', '#666', '#999', '#ccc'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

function exportReport(type) {
    window.location.href = 'api/export_report.php?type=' + type;
}

function generateFullReport() {
    alert('Generating full report... This may take a moment.');
    window.location.href = 'api/export_report.php?type=full';
}
</script>