<?php
// index.php - Main Dashboard User
require_once 'config.php';
require_once 'ForwardChaining.php';

requireLogin();

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// Get user stats
$userStats = [
    'total_diagnoses' => $db->fetchOne(
        "SELECT COUNT(*) as count FROM diagnoses WHERE user_id = ?", 
        [$user_id]
    )['count'],
    'recent_diagnosis' => $db->fetchOne(
        "SELECT * FROM diagnoses WHERE user_id = ? ORDER BY diagnosis_date DESC LIMIT 1",
        [$user_id]
    )
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #1e40af;
            --success: #10b981;
            --dark: #0f172a;
            --light: #f8fafc;
        }
        
        body {
            background: var(--light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            margin: 0 10px;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 80px 0;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            top: -200px;
            right: -200px;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-section h1 {
            font-weight: 700;
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .btn-diagnose {
            background: var(--success);
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-diagnose:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.4);
            color: white;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            margin-bottom: 30px;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin-bottom: 20px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 15px 0 5px 0;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 1rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }
        
        .recent-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 5px solid var(--primary);
        }
        
        .badge {
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .dropdown-menu {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> Car Diagnostic
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="diagnose.php">
                            <i class="fas fa-stethoscope"></i> Diagnosa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="history.php">
                            <i class="fas fa-history"></i> Riwayat
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= $_SESSION['full_name'] ?>
                        </a>
                        <ul class="dropdown-menu">
                            <?php if(isAdmin()): ?>
                            <li><a class="dropdown-item" href="admin/dashboard.php">
                                <i class="fas fa-cog"></i> Admin Panel
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container hero-content text-center">
            <h1><i class="fas fa-tools"></i> Sistem Pakar Diagnosa Mobil</h1>
            <p class="lead mb-4">Diagnosa kerusakan mobil Anda dengan teknologi Forward Chaining yang akurat</p>
            <a href="diagnose.php" class="btn btn-diagnose">
                <i class="fas fa-stethoscope"></i> Mulai Diagnosa Sekarang
            </a>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Statistics -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-number"><?= $userStats['total_diagnoses'] ?></div>
                    <div class="stat-label">Total Diagnosa Anda</div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--success), #059669);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number">
                        <?= $userStats['recent_diagnosis'] ? number_format($userStats['recent_diagnosis']['confidence_percentage'], 1) . '%' : 'N/A' ?>
                    </div>
                    <div class="stat-label">Akurasi Terakhir</div>
                </div>
            </div>
        </div>

        <!-- Features -->
        <h3 class="mb-4 mt-5"><i class="fas fa-star"></i> Fitur Unggulan</h3>
        <div class="row mb-5">
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h5>Forward Chaining</h5>
                    <p class="text-muted">Metode inferensi berbasis aturan untuk diagnosa akurat</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h5>Diagnosa Cepat</h5>
                    <p class="text-muted">Hasil diagnosa dalam hitungan detik</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5>Data Akurat</h5>
                    <p class="text-muted">Database lengkap kerusakan mobil</p>
                </div>
            </div>
        </div>

        <!-- Recent Diagnosis -->
        <?php if($userStats['recent_diagnosis']): ?>
        <h3 class="mb-4"><i class="fas fa-history"></i> Diagnosa Terakhir</h3>
        <div class="recent-card mb-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
                <div class="mb-3">
                    <h5 class="mb-2">
                        <?= $userStats['recent_diagnosis']['vehicle_brand'] ?> 
                        <?= $userStats['recent_diagnosis']['vehicle_model'] ?>
                        <?= $userStats['recent_diagnosis']['vehicle_year'] ?>
                    </h5>
                    <p class="text-muted mb-2">
                        <i class="fas fa-calendar"></i> 
                        <?= timeAgo($userStats['recent_diagnosis']['diagnosis_date']) ?>
                    </p>
                    <span class="badge bg-primary me-2">
                        <?= $userStats['recent_diagnosis']['total_symptoms'] ?> Gejala
                    </span>
                    <span class="badge bg-success">
                        Akurasi: <?= number_format($userStats['recent_diagnosis']['confidence_percentage'], 1) ?>%
                    </span>
                </div>
                <a href="result.php?id=<?= $userStats['recent_diagnosis']['diagnosis_id'] ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-eye"></i> Lihat Detail
                </a>
            </div>
        </div>
        <a href="history.php" class="btn btn-outline-primary">
            <i class="fas fa-list"></i> Lihat Semua Riwayat
        </a>
        <?php endif; ?>

        <!-- CTA -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="stat-card text-center" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white;">
                    <h3 class="mb-3">Mobil Anda Bermasalah?</h3>
                    <p class="lead mb-4">Diagnosa sekarang dan temukan solusinya!</p>
                    <a href="diagnose.php" class="btn btn-light btn-lg">
                        <i class="fas fa-stethoscope"></i> Mulai Diagnosa
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>