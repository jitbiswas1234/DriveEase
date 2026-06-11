<?php
// Note: Ensure $user is already populated or fetched before this page loads.
// If $user is not available, uncomment the line below:
// $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

$success_msg = '';
$error_msg = '';

// --- 1. HANDLE PROFILE PICTURE UPLOAD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar_upload'])) {
    $file = $_FILES['avatar_upload'];
    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($file['error'] === 0) {
        if (in_array($file_ext, $allowed_ext)) {
            if ($file['size'] < 2000000) { // 2MB Limit
                $new_name = "user_" . $user_id . "_" . time() . "." . $file_ext;
                $upload_dir = "../uploads/avatars/";
                
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_name)) {
                    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $stmt->bind_param("si", $new_name, $user_id);
                    $stmt->execute();
                    $success_msg = "Profile picture updated!";
                    $user['avatar'] = $new_name; 
                }
            } else { $error_msg = "Image is too large (Max 2MB)."; }
        } else { $error_msg = "Please upload a valid image (JPG, PNG, WebP)."; }
    }
}

// --- 2. HANDLE PROFILE DATA UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $license_number = trim($_POST['license_number'] ?? '');
    
    if (empty($name)) {
        $error_msg = 'Name is required.';
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, license_number = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $license_number, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name;
            $success_msg = 'Profile updated successfully!';
            $res = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
            $user = mysqli_fetch_assoc($res);
        } else {
            $error_msg = 'Failed to update profile.';
        }
    }
}

// --- 3. HANDLE PASSWORD CHANGE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_msg = 'All password fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error_msg = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $error_msg = 'Password must be at least 6 characters.';
    } else {
        if (password_verify($current_password, $user['password']) || $current_password === $user['password']) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            if ($stmt->execute()) {
                $success_msg = 'Password changed successfully!';
            } else {
                $error_msg = 'Failed to change password.';
            }
        } else {
            $error_msg = 'Current password is incorrect.';
        }
    }
}
?>

<style>
    .profile-grid { display: grid; grid-template-columns: 320px 1fr; gap: 2rem; align-items: start; }
    
    /* Sidebar Profile Card */
    .profile-card { background: white; border: 1px solid var(--gray-200); border-radius: 20px; padding: 2rem; text-align: center; box-shadow: var(--shadow-sm); }
    .avatar-wrapper { position: relative; width: 130px; height: 130px; margin: 0 auto 1.5rem; }
    .avatar-img { width: 130px; height: 130px; border-radius: 50%; object-fit: cover; border: 4px solid var(--gray-100); box-shadow: var(--shadow-md); }
    .avatar-initial { width: 130px; height: 130px; background: var(--primary); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3.5rem; font-weight: 700; margin: 0 auto; box-shadow: var(--shadow-md); }
    .upload-btn { position: absolute; bottom: 5px; right: 5px; background: var(--accent); color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid white; transition: var(--transition); }
    .upload-btn:hover { transform: scale(1.1); background: var(--primary); }

    .profile-stats { display: flex; justify-content: space-around; padding-top: 1.5rem; margin-top: 1.5rem; border-top: 1px solid var(--gray-100); }
    .stat-val { font-size: 1.4rem; font-weight: 800; color: var(--primary); }
    .stat-lbl { font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; }

    /* Form Section */
    .profile-section { background: white; border: 1px solid var(--gray-200); border-radius: 20px; padding: 2rem; margin-bottom: 2rem; box-shadow: var(--shadow-sm); }
    .section-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
    .section-header i { color: var(--accent); font-size: 1.2rem; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .full-width { grid-column: span 2; }
    .form-group label { display: block; margin-bottom: 0.5rem; font-size: 0.9rem; font-weight: 600; color: var(--gray-700); }
    .form-control { padding: 0.8rem 1rem; border: 1px solid var(--gray-300); border-radius: 12px; width: 100%; font-size: 0.95rem; transition: var(--transition); }
    .form-control:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(255, 59, 59, 0.1); }
    
    /* Alerts */
    .alert { padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; font-weight: 500; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    /* Mobile */
    @media (max-width: 992px) {
        .profile-grid { grid-template-columns: 1fr; }
    }
</style>

<!-- Feedback Messages -->
<?php if ($success_msg): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_msg) ?></div>
<?php endif; ?>
<?php if ($error_msg): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error_msg) ?></div>
<?php endif; ?>

<div class="profile-grid">
    <!-- Sidebar -->
    <div class="profile-card">
        <form action="" method="POST" enctype="multipart/form-data" id="avatarForm">
            <div class="avatar-wrapper">
                <?php if(!empty($user['avatar'])): ?>
                    <img src="../uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" class="avatar-img">
                <?php else: ?>
                    <div class="avatar-initial"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
                <?php endif; ?>
                
                <label for="avatar_upload" class="upload-btn">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" name="avatar_upload" id="avatar_upload" hidden onchange="document.getElementById('avatarForm').submit()">
            </div>
        </form>

        <h3 style="margin-bottom: 0.2rem;"><?= htmlspecialchars($user['name'] ?? '') ?></h3>
        <p style="color: var(--gray-500); font-size: 0.9rem; margin-bottom: 1.5rem;"><?= htmlspecialchars($user['email'] ?? '') ?></p>
        
        <div class="profile-stats">
            <div>
                <div class="stat-val"><?= $total_bookings ?? 0 ?></div>
                <div class="stat-lbl">Bookings</div>
            </div>
            <div>
                <div class="stat-val"><?= $completed_trips ?? 0 ?></div>
                <div class="stat-lbl">Trips</div>
            </div>
        </div>
    </div>

    <!-- Forms -->
    <div class="profile-content">
        <form class="profile-section" method="POST">
            <div class="section-header">
                <i class="fas fa-user"></i> <h3 style="font-size: 1.1rem;">Personal Information</h3>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>License Number</label>
                    <input type="text" name="license_number" class="form-control" value="<?= htmlspecialchars($user['license_number'] ?? '') ?>">
                </div>
                <div class="full-width">
                    <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>

        <form class="profile-section" method="POST">
            <div class="section-header">
                <i class="fas fa-lock"></i> <h3 style="font-size: 1.1rem;">Security Settings</h3>
            </div>
            <div class="form-grid">
                <div class="full-width">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control">
                </div>
                <div class="full-width">
                    <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
                </div>
            </div>
        </form>
    </div>
</div>