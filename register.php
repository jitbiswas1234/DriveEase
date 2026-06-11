<?php
session_start();
require_once 'config/database.php';
require_once 'config/base_url.php';

if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])) {
    header("Location: " . $base_url . "index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = trim($_POST['name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $license_number = trim($_POST['license_number'] ?? '');
    $password       = $_POST['password'] ?? '';
    $confirm_pass   = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_pass) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, license_number) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $hashed, $phone, $license_number);
            
            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
                header("Refresh: 2; url=login.php");
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DriveEase</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #000000;
            --accent: #ff4444;
        }
        body { background: #f5f5f5; font-family: 'Inter', sans-serif; }
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 120px 20px 60px;
        }
        .register-card {
            background: white;
            max-width: 520px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .register-header {
            background: var(--primary);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .register-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.1rem;
        }
        .register-body {
            padding: 2.5rem;
        }
        .form-group {
            margin-bottom: 1.4rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #444;
        }
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #ddd;
            border-radius: 12px;
            font-size: 1rem;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
        }
        .btn-register {
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
        }
        .btn-register:hover {
            background: #222;
        }
        .alert {
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        .alert-error { background: #fee2e2; color: #dc2626; }
        .alert-success { background: #d1fae5; color: #166534; }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="register-container">
    <div class="register-card">
        <div class="register-header">
            <h1>Create Account</h1>
            <p>Join DriveEase today</p>
        </div>
        
        <div class="register-body">
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Driving License Number</label>
                    <input type="text" name="license_number" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn-register">Create Account</button>
            </form>

            <div class="login-link">
                Already have an account? 
                <a href="login.php"><strong>Login Here</strong></a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>