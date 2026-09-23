<?php
// index.php - Login & Landing Page
require_once __DIR__ . '/config/db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if (isset($_GET['logged_out'])) {
    $success = "You have been logged out successfully.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username OR email = :email LIMIT 1");
        $stmt->execute([
            ':username' => $username,
            ':email'    => $username
        ]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_fullname'] = $admin['fullname'];
            $_SESSION['admin_email'] = $admin['email'];

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username/email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Record Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            max-width: 440px;
            width: 100%;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #ffffff;
            padding: 36px 30px 28px;
            text-align: center;
        }
        .login-header .icon-circle {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.18);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 12px;
            backdrop-filter: blur(4px);
        }
        .login-body {
            padding: 36px 32px 32px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 0.95rem;
            border: 1px solid #cbd5e1;
        }
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.2s ease;
        }
        .btn-primary-custom:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
        }
        .demo-badge {
            background-color: #f1f5f9;
            border: 1px dashed #94a3b8;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.82rem;
            color: #475569;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="icon-circle">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <h3 class="fw-bold mb-1">Student Record Portal</h3>
        <p class="mb-0 text-white-50" style="font-size: 0.9rem;">Sign in to access student management</p>
    </div>

    <div class="login-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center mb-3" role="alert" style="border-radius: 10px; font-size: 0.9rem;">
                <i class="fa-solid fa-circle-exclamation me-2"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success d-flex align-items-center mb-3" role="alert" style="border-radius: 10px; font-size: 0.9rem;">
                <i class="fa-solid fa-circle-check me-2"></i>
                <div><?php echo htmlspecialchars($success); ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <div class="mb-3">
                <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">USERNAME OR EMAIL</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                        <i class="fa-solid fa-user text-muted"></i>
                    </span>
                    <input type="text" name="username" class="form-control border-start-0" placeholder="Enter username or email" value="<?php echo htmlspecialchars($_POST['username'] ?? 'admin'); ?>" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">PASSWORD</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px; border-color: #cbd5e1;">
                        <i class="fa-solid fa-lock text-muted"></i>
                    </span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="Enter password" value="admin123" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary-custom mb-3">
                <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Sign In to Dashboard
            </button>
        </form>

        <div class="demo-badge text-center mt-3">
            <div class="fw-bold mb-1"><i class="fa-solid fa-key me-1 text-primary"></i> Default Demo Credentials:</div>
            <div>Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>admin123</code></div>
        </div>
    </div>
</div>

</body>
</html>
