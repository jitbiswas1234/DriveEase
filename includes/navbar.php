<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

require_once(__DIR__.'/../config/base_url.php');
// Ensure database connection is available for the avatar fetch
require_once(__DIR__.'/../config/database.php'); 

// --- NEW LOGIC: DETECT DASHBOARD SECTION ---
$current_uri = $_SERVER['REQUEST_URI'];
$isDashboard = (strpos($current_uri, 'user/index.php') !== false);

$isAdmin = isset($_SESSION['admin_id']);
$isUser = isset($_SESSION['user_id']);
$isLoggedIn = $isAdmin || $isUser;

$userName='';
$userAvatar = '';

if($isAdmin){
    $userName=$_SESSION['admin_username'];
}
elseif($isUser){
    $userName=$_SESSION['user_name'];
    $user_id = $_SESSION['user_id'];
    
    // Fetch avatar from database
    $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($u = $res->fetch_assoc()){
        $userAvatar = $u['avatar'];
    }
}
?>

<nav class="navbar <?php echo $isDashboard ? 'dashboard-nav' : ''; ?>">
    <div class="nav-container">
        <a href="<?php echo $base_url; ?>" class="logo">
            <i class="fas fa-car"></i>
            <span>Drive<span class="red">Ease</span></span>
        </a>

        <ul class="nav-menu" id="navMenu">
            <li><a href="<?php echo $base_url; ?>">Home</a></li>
            <li><a href="<?php echo $base_url; ?>user/cars.php">Browse Cars</a></li>
            <li><a href="<?php echo $base_url; ?>#features">Features</a></li>
            <li><a href="<?php echo $base_url; ?>contact.php">Contact Us</a></li>

            <?php if($isLoggedIn): ?>
                <?php if($isAdmin): ?>
                    <li><a href="<?php echo $base_url; ?>admin/index.php?page=dashboard">Admin Panel</a></li>
                <?php else: ?>
                    <li><a href="<?php echo $base_url; ?>user/index.php?page=dashboard">Dashboard</a></li>
                    <li><a href="<?php echo $base_url; ?>user/index.php?page=bookings">My Bookings</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>

        <div class="nav-buttons" id="navButtons">
            <?php if($isLoggedIn): ?>
                <span class="user" style="display: flex; align-items: center; gap: 10px;">
                    <?php if(!empty($userAvatar) && file_exists(__DIR__.'/../uploads/avatars/'.$userAvatar)): ?>
                        <img src="<?php echo $base_url; ?>uploads/avatars/<?php echo $userAvatar; ?>" 
                             style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid #eee;">
                    <?php else: ?>
                        <i class="fas fa-user-circle" style="font-size: 1.8rem;"></i>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($userName); ?>
                </span>
                <a href="<?php echo $base_url; ?>logout.php" class="btn outline">Logout</a>
            <?php else: ?>
                <a href="<?php echo $base_url; ?>login.php" class="btn outline">Login</a>
                <a href="<?php echo $base_url; ?>register.php" class="btn solid">Register</a>
            <?php endif; ?>
        </div>

        <div class="hamburger" id="hamburger">
            <span></span><span></span><span></span>
        </div>
    </div>
</nav>

<style>
/* --- BASE NAVBAR STYLES --- */
.navbar {
    position: fixed;
    top: 0;
    width: 100%;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(12px);
    padding: 15px 5%;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    z-index: 1001; /* Higher than sidebar */
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* --- ADJUST WIDTH FOR DASHBOARD --- */
.navbar.dashboard-nav {
    left: 260px; 
    width: calc(100% - 260px);
    padding: 15px 30px;
    border-left: 1px solid #f0f0f0;
}

.nav-container {
    max-width: 1300px;
    margin: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* --- EXISTING STYLES --- */
.logo { display: flex; align-items: center; gap: 10px; text-decoration: none; font-size: 22px; font-weight: 700; color: black; }
.red { color: #ff3b3b; }
.nav-menu { display: flex; list-style: none; gap: 30px; }
.nav-menu a { text-decoration: none; color: #333; font-weight: 500; transition: .3s; }
.nav-menu a:hover { color: #ff3b3b; }
.nav-buttons { display: flex; align-items: center; gap: 15px; }
.user { font-weight: 600; color: #333; }
.btn { padding: 8px 20px; border-radius: 30px; text-decoration: none; font-weight: 600; cursor: pointer; border: none; }
.outline { border: 2px solid black; color: black; background: transparent; }
.outline:hover { background: black; color: white; }
.solid { background: black; color: white; }
.solid:hover { background: #222; }
.hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; }
.hamburger span { width: 25px; height: 3px; background: black; }

/* MOBILE RESPONSIVE */
@media(max-width: 992px) {
    .navbar.dashboard-nav {
        left: 0;
        width: 100%;
        padding: 15px 5%;
    }
    .nav-menu { position: fixed; top: 70px; left: -100%; width: 100%; height: auto; background: white; flex-direction: column; align-items: center; gap: 25px; padding: 30px 0; transition: .4s; }
    .nav-menu.active { left: 0; }
    .nav-buttons { position: fixed; top: 280px; left: -100%; width: 100%; flex-direction: column; align-items: center; gap: 15px; transition: .4s; }
    .nav-buttons.active { left: 0; }
    .hamburger { display: flex; }
}
</style>

<script>
const hamburger = document.getElementById("hamburger");
const navMenu = document.getElementById("navMenu");
const navButtons = document.getElementById("navButtons");

hamburger.addEventListener("click", () => {
    navMenu.classList.toggle("active");
    navButtons.classList.toggle("active");
});
</script>