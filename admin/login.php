<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/path.php';
require_once __DIR__ . '/../config/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM akun WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && $password === $user['password']) {
            // Check if user is admin
            if ($user['role'] === 'admin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                header('Location: ' . url('admin/dashboard.php'));
                exit();
            } else {
                $error = 'Akses ditolak. Halaman ini khusus untuk admin.';
            }
        } else {
            $error = 'Username atau password salah.';
        }
    }
}

$page_title = 'Admin Login - Veloce';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1f3b83 0%, #5b8af0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .login-header {
            background: linear-gradient(135deg, #1f3b83 0%, #5b8af0 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .login-header i {
            font-size: 60px;
            margin-bottom: 15px;
        }

        .login-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #ecf0ff;
            border-radius: 10px;
            font-size: 15px;
            transition: border-color 0.3s;
            outline: none;
        }

        .form-control:focus {
            border-color: #5b8af0;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1f3b83 0%, #5b8af0 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(31, 59, 131, 0.3);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fdecea;
            color: #b0413e;
            border: 1px solid #f5c6cb;
        }

        .login-footer {
            text-align: center;
            padding: 20px 30px;
            background: #f8faff;
            border-top: 1px solid #ecf0ff;
        }

        .login-footer a {
            color: #5b8af0;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .login-footer a:hover {
            color: #1f3b83;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            color: #999;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-user-shield"></i>
            <h1>Admin Login</h1>
            <p>Halaman khusus administrator Veloce</p>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username Admin</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" class="form-control" 
                               placeholder="Masukkan username admin" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Masukkan password" required>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login sebagai Admin
                </button>
            </form>
        </div>
    </div>
</body>
</html>
