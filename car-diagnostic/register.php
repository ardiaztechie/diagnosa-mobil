<?php
// register.php - User Registration
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = 'Semua field wajib diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok';
    } else {
        $db = Database::getInstance();
        
        $check = $db->fetchOne("SELECT user_id FROM users WHERE username = ?", [$username]);
        if ($check) {
            $error = 'Username sudah digunakan';
        } else {
            $check = $db->fetchOne("SELECT user_id FROM users WHERE email = ?", [$email]);
            if ($check) {
                $error = 'Email sudah terdaftar';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $sql = "INSERT INTO users (username, email, password, full_name, phone, role) 
                        VALUES (?, ?, ?, ?, ?, 'user')";
                
                if ($db->query($sql, [$username, $email, $hashed_password, $full_name, $phone])) {
                    $user_id = $db->lastInsertId();
                    logActivity($user_id, 'registration', 'New user registered');
                    redirect('login.php?registered=1');
                } else {
                    $error = 'Terjadi kesalahan saat registrasi';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #1e40af;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-wrapper {
            width: 100%;
            max-width: 600px;
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(0,0,0,0.25);
        }
        
        .register-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .register-header i {
            font-size: 50px;
            margin-bottom: 15px;
        }
        
        .register-body {
            padding: 40px 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 8px;
        }
        
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        
        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
        
        .form-control {
            padding: 14px 20px 14px 50px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }
        
        .btn-register {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.3);
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #64748b;
        }
        
        .login-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <div class="register-header">
            <i class="fas fa-user-plus"></i>
            <h2 class="mb-0">Daftar Akun Baru</h2>
            <p class="mb-0">Buat akun untuk diagnosa mobil Anda</p>
        </div>
        
        <div class="register-body">
            <?php if($error): ?>
                <div class="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <div class="input-group">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="username" class="form-control" 
                               placeholder="Pilih username" required value="<?= $_POST['username'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" class="form-control" 
                               placeholder="email@example.com" required value="<?= $_POST['email'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <div class="input-group">
                        <i class="fas fa-id-card input-icon"></i>
                        <input type="text" name="full_name" class="form-control" 
                               placeholder="Nama lengkap Anda" required value="<?= $_POST['full_name'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <div class="input-group">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="text" name="phone" class="form-control" 
                               placeholder="08xxxxxxxxxx" value="<?= $_POST['phone'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control" 
                               placeholder="Min. 6 karakter" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="confirm_password" class="form-control" 
                               placeholder="Ulangi password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button>
            </form>
            
            <div class="login-link">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>