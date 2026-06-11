<?php
session_start();
require_once 'config/database.php';
require_once 'config/base_url.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "user/index.php");
    exit();
}
if (isset($_SESSION['admin_id'])) {
    header("Location: " . $base_url . "admin/dashboard.php");
    exit();
}

$error = '';
$loginType = isset($_GET['type']) ? $_GET['type'] : 'user';

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $userType = $_POST['user_type'] ?? 'user';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        if ($userType === 'admin') {
            // Admin Login
            $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password']) || $password === $admin['password']) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['user_role'] = 'admin';
                    header("Location: " . $base_url . "admin/index.php");
                    exit();
                }
            }
            $error = "Invalid username or password.";
            $stmt->close();
        } else {
            // User Login
            $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password']) || $password === $user['password']) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = 'user';
                    header("Location: " . $base_url . "user/index.php");
                    exit();
                }
            }
            $error = "Invalid email or password.";
            $stmt->close();
        }
    }
}

$pageTitle = "Login";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DriveEase</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #000000;
            --accent: #ff4444;
            --gray-100: #f5f5f5;
            --gray-200: #e5e5e5;
            --gray-500: #737373;
            --gray-600: #525252;
        }

        body {
            background: var(--gray-100);
            font-family: 'Inter', sans-serif;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 20px 60px;
        }

        .login-card {
            background: white;
            max-width: 480px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .login-header {
            background: var(--primary);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .login-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }

        .login-body {
            padding: 2.5rem;
        }

        .toggle-container {
            display: flex;
            background: var(--gray-100);
            border-radius: 12px;
            padding: 6px;
            margin-bottom: 2rem;
        }

        .toggle-btn {
            flex: 1;
            padding: 12px;
            border: none;
            background: transparent;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .toggle-btn.active {
            background: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            color: var(--primary);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--gray-600);
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0,0,0,0.05);
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--gray-500);
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: #1a1a1a;
            transform: translateY(-2px);
        }

        .alert {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .alert-error {
            background: #fee2e2;
            color: #dc2626;
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray-600);
        }

        .register-link a {
            color: var(--primary);
            font-weight: 600;
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="login-container">
    <div class="login-card">
        
        <!-- Header -->
        <div class="login-header">
            <h1>Sign In</h1>
            <p>Welcome back to DriveEase</p>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Toggle User / Admin -->
            <div class="toggle-container">
                <button type="button" class="toggle-btn <?= $loginType === 'user' ? 'active' : '' ?>" onclick="switchType('user')">
                    <i class="fas fa-user"></i> Customer
                </button>
                <button type="button" class="toggle-btn <?= $loginType === 'admin' ? 'active' : '' ?>" onclick="switchType('admin')">
                    <i class="fas fa-user-shield"></i> Admin
                </button>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="user_type" id="userType" value="<?= $loginType ?>">

                <div class="form-group">
                    <label for="email">
                        <?= $loginType === 'admin' ? 'Username' : 'Email Address' ?>
                    </label>
                    <input type="<?= $loginType === 'admin' ? 'text' : 'email' ?>" 
                           name="email" 
                           id="email" 
                           class="form-control" 
                           placeholder="<?= $loginType === 'admin' ? 'Enter username' : 'Enter your email' ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="register-link">
                Don't have an account? 
                <a href="register.php">Create Account</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function switchType(type) {
    document.getElementById('userType').value = type;
    window.location.href = '?type=' + type;
}

function togglePassword() {
    const pass = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    
    if (pass.type === "password") {
        pass.type = "text";
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        pass.type = "password";
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Keep toggle active state after reload
document.addEventListener('DOMContentLoaded', () => {
    const currentType = '<?= $loginType ?>';
    const buttons = document.querySelectorAll('.toggle-btn');
    buttons.forEach(btn => {
        if (btn.getAttribute('onclick').includes(currentType)) {
            btn.classList.add('active');
        }
    });
});
</script>

</body>
</html>