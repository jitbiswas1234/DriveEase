<?php
session_start();
require_once '../config/database.php';
require_once '../config/base_url.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $base_url . 'login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';

// Get current page
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Fetch user data
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DriveEase</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #000000;
            --primary-light: #1a1a1a;
            --secondary: #ffffff;
            --accent: #ff4444;
            --success: #22c55e;
            --warning: #f59e0b;
            --info: #3b82f6;
            --danger: #dc2626;
            --gray-50: #fafafa;
            --gray-100: #f5f5f5;
            --gray-200: #e5e5e5;
            --gray-300: #d4d4d4;
            --gray-400: #a3a3a3;
            --gray-500: #737373;
            --gray-600: #525252;
            --gray-700: #404040;
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1);
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

        /* Layout */
        .user-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Fixed Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: var(--secondary);
            border-right: 1px solid var(--gray-200);
            padding: 2rem 1.5rem;
            overflow-y: auto;
            z-index: 1000;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 1.5rem;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.5rem;
        }

        .sidebar-logo-icon {
            width: 45px;
            height: 45px;
            background: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }

        .sidebar-logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
        }

        .sidebar-logo-text span { color: var(--accent); }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: 12px;
        }

        .sidebar-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--gray-700));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .sidebar-user-info h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.2rem;
        }

        .sidebar-user-info p {
            font-size: 0.8rem;
            color: var(--gray-500);
        }

        .sidebar-menu {
            list-style: none;
            flex: 1;
        }

        .menu-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--gray-400);
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1rem 0 0.5rem;
            margin-top: 1rem;
        }

        .sidebar-menu li { margin-bottom: 0.3rem; }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.9rem 1rem;
            border-radius: 10px;
            color: var(--gray-600);
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
        }

        .sidebar-menu a:hover {
            background: var(--gray-100);
            color: var(--primary);
        }

        .sidebar-menu a.active {
            background: var(--primary);
            color: white;
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }

        .sidebar-footer {
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
            margin-top: auto;
        }

        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.9rem 1rem;
            border-radius: 10px;
            color: var(--danger);
            font-size: 0.9rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .sidebar-footer a:hover {
            background: rgba(220, 38, 38, 0.1);
        }

        /* Main Content */
        .main-wrapper {
            flex: 1;
            margin-left: 280px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-bar {
            background: var(--secondary);
            border-bottom: 1px solid var(--gray-200);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-info h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .page-info p {
            font-size: 0.9rem;
            color: var(--gray-500);
        }

        .top-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.2rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--gray-300);
            color: var(--gray-700);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .content-area {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .page-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .page-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.9);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .loading-overlay.active {
            display: flex;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid var(--gray-200);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Mobile Toggle */
        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
            }

            .mobile-toggle {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 1.5rem;
            }

            .top-bar {
                padding: 1rem 1.5rem;
            }
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 0.3rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--gray-500);
        }

        /* Card */
        .card {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--gray-200);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        .data-table th {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            background: var(--gray-50);
        }

        .data-table tr:hover {
            background: var(--gray-50);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-badge.pending { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .status-badge.confirmed { background: rgba(59, 130, 246, 0.1); color: var(--info); }
        .status-badge.completed { background: rgba(34, 197, 94, 0.1); color: var(--success); }
        .status-badge.cancelled { background: rgba(220, 38, 38, 0.1); color: var(--danger); }
        .status-badge.paid { background: rgba(34, 197, 94, 0.1); color: var(--success); }

        /* Mobile overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }

        .sidebar-overlay.active {
            display: block;
        }
    </style>
</head>
<body>

<div class="user-layout">
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Fixed Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo $base_url; ?>" class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div class="sidebar-logo-text">Drive<span>Ease</span></div>
            </a>
            
            <div class="sidebar-user">
                <div class="sidebar-avatar"><?php echo strtoupper(substr($userName, 0, 1)); ?></div>
                <div class="sidebar-user-info">
                    <h4><?php echo htmlspecialchars($userName); ?></h4>
                    <p><?php echo htmlspecialchars($userEmail); ?></p>
                </div>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-label">Main Menu</li>
            <li>
                <a onclick="loadPage('dashboard')" class="nav-link <?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>" data-page="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a onclick="loadPage('browse')" class="nav-link <?php echo $currentPage == 'browse' ? 'active' : ''; ?>" data-page="browse">
                    <i class="fas fa-car"></i>
                    <span>Browse Cars</span>
                </a>
            </li>
            <li>
                <a onclick="loadPage('bookings')" class="nav-link <?php echo $currentPage == 'bookings' ? 'active' : ''; ?>" data-page="bookings">
                    <i class="fas fa-calendar-check"></i>
                    <span>My Bookings</span>
                </a>
            </li>

            <li class="menu-label">Account</li>
            <li>
                <a onclick="loadPage('profile')" class="nav-link <?php echo $currentPage == 'profile' ? 'active' : ''; ?>" data-page="profile">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li>
                <a onclick="loadPage('payments')" class="nav-link <?php echo $currentPage == 'payments' ? 'active' : ''; ?>" data-page="payments">
                    <i class="fas fa-credit-card"></i>
                    <span>Payment History</span>
                </a>
            </li>

            <li class="menu-label">Support</li>
            <li>
               <a href="<?php echo $base_url; ?>index.php" class="btn btn-primary">
                    <i class="fas fa-car"></i> back to website
                </a>
            </li>
            <li>
                <a href="<?php echo $base_url; ?>contact.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>need help</span>
            </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="<?php echo $base_url; ?>logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
        <div class="top-bar">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="mobile-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-info">
                    <h1 id="pageTitle">Dashboard</h1>
                    <p id="pageSubtitle">Welcome back, <?php echo htmlspecialchars($userName); ?>!</p>
                </div>
            </div>
            <div class="top-actions">
                <a href="<?php echo $base_url; ?>user/cars.php" class="btn btn-primary">
                    <i class="fas fa-car"></i> Book a Car
                </a>
            </div>
        </div>

        <div class="content-area" id="contentArea">
            <!-- Dashboard -->
            <div id="page-dashboard" class="page-content active">
                <?php include 'pages/dashboard.php'; ?>
            </div>

            <!-- Browse Cars -->
            <div id="page-browse" class="page-content">
                <?php include 'pages/browse.php'; ?>
            </div>

            <!-- My Bookings -->
            <div id="page-bookings" class="page-content">
                <?php include 'pages/my_bookings.php'; ?>
            </div>

            <!-- Profile -->
            <div id="page-profile" class="page-content">
                <?php include 'pages/profile.php'; ?>
            </div>

            <!-- Payment History -->
            <div id="page-payments" class="page-content">
                <?php include 'pages/payments.php'; ?>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<script>
const pageConfig = {
    'dashboard': { title: 'Dashboard', subtitle: 'Welcome back, <?php echo htmlspecialchars($userName); ?>!' },
    'browse': { title: 'Browse Cars', subtitle: 'Find your perfect ride' },
    'bookings': { title: 'My Bookings', subtitle: 'View and manage your bookings' },
    'profile': { title: 'Profile', subtitle: 'Manage your account settings' },
    'payments': { title: 'Payment History', subtitle: 'View your payment history' }
};

function loadPage(pageName) {
    document.getElementById('loadingOverlay').classList.add('active');
    
    // Update active state
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
        if (link.dataset.page === pageName) {
            link.classList.add('active');
        }
    });
    
    // Hide all pages
    document.querySelectorAll('.page-content').forEach(page => {
        page.classList.remove('active');
    });
    
    // Show selected page
    const targetPage = document.getElementById('page-' + pageName);
    if (targetPage) {
        targetPage.classList.add('active');
        
        const config = pageConfig[pageName] || {};
        document.getElementById('pageTitle').textContent = config.title || pageName;
        document.getElementById('pageSubtitle').textContent = config.subtitle || '';
        
        history.pushState({ page: pageName }, pageName, '?page=' + pageName);
    }
    
    setTimeout(() => {
        document.getElementById('loadingOverlay').classList.remove('active');
    }, 300);
    
    // Close mobile sidebar
    if (window.innerWidth <= 1024) {
        document.getElementById('sidebar').classList.remove('active');
        document.getElementById('sidebarOverlay').classList.remove('active');
    }
}

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('sidebarOverlay').classList.toggle('active');
}

// Handle browser back/forward
window.onpopstate = function(event) {
    if (event.state && event.state.page) {
        loadPage(event.state.page);
    }
};

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('page') || 'dashboard';
    loadPage(page);
});
</script>

</body>
</html>