<?php
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';

// Build query
$where_conditions = [];

if ($search) {
    $search_safe = mysqli_real_escape_string($conn, $search);
    $where_conditions[] = "(car_name LIKE '%$search_safe%' OR brand LIKE '%$search_safe%' OR model LIKE '%$search_safe%')";
}

if ($status_filter && $status_filter !== 'all') {
    $status_safe = mysqli_real_escape_string($conn, $status_filter);
    $where_conditions[] = "status = '$status_safe'";
}

if ($type_filter && $type_filter !== 'all') {
    $type_safe = mysqli_real_escape_string($conn, $type_filter);
    $where_conditions[] = "car_type = '$type_safe'";
}

$where_clause = count($where_conditions) > 0 ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count stats
$total_cars = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cars"))['count'];
$available_cars = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cars WHERE status = 'Available'"))['count'];
$rented_cars = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cars WHERE status = 'Rented'"))['count'];
$maintenance_cars = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM cars WHERE status = 'Maintenance'"))['count'];

// Get car types for filter
$car_types = mysqli_query($conn, "SELECT DISTINCT car_type FROM cars ORDER BY car_type");

// Fetch cars
$cars = mysqli_query($conn, "SELECT * FROM cars $where_clause ORDER BY created_at DESC");

// Check for success/error messages
$success_msg = isset($_GET['success']) ? $_GET['success'] : '';
$error_msg = isset($_GET['error']) ? $_GET['error'] : '';
?>

<style>
    .cars-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .cars-stats {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .mini-stat {
        background: white;
        padding: 0.8rem 1.2rem;
        border-radius: 10px;
        border: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .mini-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .mini-stat-value {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--primary);
        line-height: 1;
    }

    .mini-stat-label {
        font-size: 0.75rem;
        color: var(--gray-500);
    }

    .filter-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }

    .search-box input {
        width: 100%;
        padding: 0.8rem 1rem 0.8rem 2.5rem;
        border: 1px solid var(--gray-200);
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.3s;
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
    }

    .filter-select {
        padding: 0.8rem 1rem;
        border: 1px solid var(--gray-200);
        border-radius: 10px;
        font-size: 0.95rem;
        background: white;
        min-width: 150px;
        cursor: pointer;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--primary);
    }

    .car-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .car-card {
        background: white;
        border-radius: 16px;
        border: 1px solid var(--gray-200);
        overflow: hidden;
        transition: all 0.3s;
    }

    .car-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-xl);
    }

    .car-image {
        height: 180px;
        background: var(--gray-100);
        position: relative;
        overflow: hidden;
    }

    .car-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .car-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: var(--gray-300);
        background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
    }

    .car-status {
        position: absolute;
        top: 1rem;
        left: 1rem;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .car-status.available {
        background: rgba(34, 197, 94, 0.9);
        color: white;
    }

    .car-status.rented {
        background: rgba(59, 130, 246, 0.9);
        color: white;
    }

    .car-status.maintenance {
        background: rgba(245, 158, 11, 0.9);
        color: white;
    }

    .car-type-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.4rem 0.8rem;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .car-content {
        padding: 1.5rem;
    }

    .car-brand {
        font-size: 0.8rem;
        color: var(--gray-500);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.3rem;
    }

    .car-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.5rem;
    }

    .car-specs {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
        flex-wrap: wrap;
    }

    .car-spec {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.85rem;
        color: var(--gray-600);
    }

    .car-spec i {
        color: var(--gray-400);
        font-size: 0.8rem;
    }

    .car-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid var(--gray-200);
    }

    .car-price {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--primary);
    }

    .car-price span {
        font-size: 0.85rem;
        font-weight: 400;
        color: var(--gray-500);
    }

    .car-actions {
        display: flex;
        gap: 0.5rem;
    }

    .car-actions .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid var(--gray-200);
        background: white;
        color: var(--gray-600);
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .car-actions .action-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .car-actions .action-btn.delete:hover {
        background: var(--danger);
        border-color: var(--danger);
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
        border: 1px solid var(--gray-200);
    }

    .empty-state i {
        font-size: 4rem;
        color: var(--gray-300);
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: var(--gray-600);
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: var(--gray-500);
        margin-bottom: 1.5rem;
    }

    .alert {
        padding: 1rem 1.5rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .alert-success {
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
    }

    .alert-error {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    /* Delete Modal */
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
        max-width: 400px;
        margin: 1rem;
        text-align: center;
        padding: 2rem;
    }

    .modal-icon {
        width: 60px;
        height: 60px;
        background: #fef2f2;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
        color: var(--danger);
    }

    .modal h3 {
        margin-bottom: 0.5rem;
        color: var(--primary);
    }

    .modal p {
        color: var(--gray-500);
        margin-bottom: 1.5rem;
    }

    .modal-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }

    .view-toggle {
        display: flex;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        overflow: hidden;
    }

    .view-toggle button {
        padding: 0.6rem 1rem;
        border: none;
        background: white;
        cursor: pointer;
        transition: all 0.3s;
    }

    .view-toggle button.active {
        background: var(--primary);
        color: white;
    }

    @media (max-width: 768px) {
        .cars-header {
            flex-direction: column;
            align-items: stretch;
        }

        .cars-stats {
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }

        .filter-row {
            flex-direction: column;
        }

        .search-box {
            width: 100%;
        }
    }
</style>

<!-- Success/Error Messages -->
<?php if ($success_msg): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($success_msg); ?>
    </div>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($error_msg); ?>
    </div>
<?php endif; ?>

<!-- Stats Header -->
<div class="cars-header">
    <div class="cars-stats">
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: var(--gray-100); color: var(--primary);">
                <i class="fas fa-car"></i>
            </div>
            <div>
                <div class="mini-stat-value"><?php echo $total_cars; ?></div>
                <div class="mini-stat-label">Total Cars</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(34, 197, 94, 0.1); color: var(--success);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="mini-stat-value"><?php echo $available_cars; ?></div>
                <div class="mini-stat-label">Available</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(59, 130, 246, 0.1); color: var(--info);">
                <i class="fas fa-key"></i>
            </div>
            <div>
                <div class="mini-stat-value"><?php echo $rented_cars; ?></div>
                <div class="mini-stat-label">Rented</div>
            </div>
        </div>
        <div class="mini-stat">
            <div class="mini-stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--warning);">
                <i class="fas fa-wrench"></i>
            </div>
            <div>
                <div class="mini-stat-value"><?php echo $maintenance_cars; ?></div>
                <div class="mini-stat-label">Maintenance</div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary" onclick="window.location.href='add_car.php'">
        <i class="fas fa-plus"></i> Add New Car
    </button>
</div>

<!-- Filters -->
<div class="filter-row">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Search cars..." value="<?php echo htmlspecialchars($search); ?>">
    </div>
    
    <select class="filter-select" id="statusFilter" onchange="applyFilters()">
        <option value="all" <?php echo $status_filter == '' || $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
        <option value="Available" <?php echo $status_filter == 'Available' ? 'selected' : ''; ?>>Available</option>
        <option value="Rented" <?php echo $status_filter == 'Rented' ? 'selected' : ''; ?>>Rented</option>
        <option value="Maintenance" <?php echo $status_filter == 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
    </select>

    <select class="filter-select" id="typeFilter" onchange="applyFilters()">
        <option value="all" <?php echo $type_filter == '' || $type_filter == 'all' ? 'selected' : ''; ?>>All Types</option>
        <?php mysqli_data_seek($car_types, 0); ?>
        <?php while ($type = mysqli_fetch_assoc($car_types)): ?>
            <option value="<?php echo htmlspecialchars($type['car_type']); ?>" <?php echo $type_filter == $type['car_type'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($type['car_type']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <div class="view-toggle">
        <button class="active" onclick="setView('grid')"><i class="fas fa-th"></i></button>
        <button onclick="setView('list')"><i class="fas fa-list"></i></button>
    </div>
</div>

<!-- Cars Grid -->
<?php if (mysqli_num_rows($cars) > 0): ?>
    <div class="car-grid" id="carGrid">
        <?php while ($car = mysqli_fetch_assoc($cars)): ?>
            <div class="car-card" data-id="<?php echo $car['id']; ?>">
                <div class="car-image">
                    <?php if (!empty($car['image']) && file_exists('../uploads/car_images/' . $car['image'])): ?>
                        <img src="<?php echo $base_url; ?>uploads/car_images/<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['car_name']); ?>">
                    <?php else: ?>
                        <div class="car-image-placeholder">
                            <i class="fas fa-car"></i>
                        </div>
                    <?php endif; ?>
                    <span class="car-status <?php echo strtolower($car['status']); ?>">
                        <?php echo $car['status']; ?>
                    </span>
                    <span class="car-type-badge"><?php echo htmlspecialchars($car['car_type']); ?></span>
                </div>
                <div class="car-content">
                    <div class="car-brand"><?php echo htmlspecialchars($car['brand']); ?></div>
                    <h3 class="car-name"><?php echo htmlspecialchars($car['car_name']); ?></h3>
                    <div class="car-specs">
                        <span class="car-spec">
                            <i class="fas fa-calendar"></i>
                            <?php echo $car['year']; ?>
                        </span>
                        <span class="car-spec">
                            <i class="fas fa-gas-pump"></i>
                            <?php echo htmlspecialchars($car['fuel_type']); ?>
                        </span>
                        <span class="car-spec">
                            <i class="fas fa-cog"></i>
                            <?php echo htmlspecialchars($car['transmission']); ?>
                        </span>
                        <span class="car-spec">
                            <i class="fas fa-users"></i>
                            <?php echo $car['seats']; ?> seats
                        </span>
                    </div>
                    <div class="car-footer">
                        <div class="car-price">
                            $<?php echo number_format($car['price_per_day'], 0); ?><span>/day</span>
                        </div>
                        <div class="car-actions">
                            <button class="action-btn" title="View" onclick="viewCar(<?php echo $car['id']; ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn" title="Edit" onclick="editCar(<?php echo $car['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete" title="Delete" onclick="confirmDelete(<?php echo $car['id']; ?>, '<?php echo htmlspecialchars(addslashes($car['car_name'])); ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="empty-state">
        <i class="fas fa-car"></i>
        <h3>No Cars Found</h3>
        <p>There are no cars matching your criteria. Try adjusting your filters or add a new car.</p>
        <button class="btn btn-primary" onclick="window.location.href='add_car.php'">
            <i class="fas fa-plus"></i> Add First Car
        </button>
    </div>
<?php endif; ?>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <div class="modal-icon">
            <i class="fas fa-trash"></i>
        </div>
        <h3>Delete Car</h3>
        <p>Are you sure you want to delete "<span id="deleteCarName"></span>"? This action cannot be undone.</p>
        <div class="modal-actions">
            <button class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn" style="background: var(--danger); color: white;" onclick="deleteCar()">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>

<script>
let deleteCarId = null;

// Search functionality
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
});

// Apply filters
function applyFilters() {
    const search = document.getElementById('searchInput').value;
    const status = document.getElementById('statusFilter').value;
    const type = document.getElementById('typeFilter').value;
    
    const params = new URLSearchParams();
    params.set('page', 'cars');
    if (search) params.set('search', search);
    if (status && status !== 'all') params.set('status', status);
    if (type && type !== 'all') params.set('type', type);
    
    window.location.href = 'index.php?' + params.toString();
}

// View car details
function viewCar(id) {
    window.location.href = 'view_car.php?id=' + id;
}

// Edit car - THIS IS THE FIX!
function editCar(id) {
    window.location.href = 'edit_car.php?id=' + id;
}

// Delete car confirmation
function confirmDelete(id, name) {
    deleteCarId = id;
    document.getElementById('deleteCarName').textContent = name;
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    deleteCarId = null;
}

function deleteCar() {
    if (!deleteCarId) return;
    
    fetch('api/delete_car.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + deleteCarId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'index.php?page=cars&success=' + encodeURIComponent('Car deleted successfully');
        } else {
            alert(data.message || 'Failed to delete car');
        }
        closeDeleteModal();
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        closeDeleteModal();
    });
}

// View toggle
function setView(view) {
    const grid = document.getElementById('carGrid');
    const buttons = document.querySelectorAll('.view-toggle button');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.closest('button').classList.add('active');
    
    if (view === 'list') {
        grid.style.gridTemplateColumns = '1fr';
    } else {
        grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(300px, 1fr))';
    }
}

// Auto-hide alerts after 5 seconds
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
});
</script>