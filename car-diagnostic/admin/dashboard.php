<?php
// admin/dashboard.php - Admin Dashboard
require_once '../config.php';
require_once '../ForwardChaining.php';

requireAdmin();

$db = Database::getInstance();

// Get statistics
$stats = ForwardChaining::getStatistics();

// Get recent diagnoses
$recentDiagnoses = $db->fetchAll(
    "SELECT d.*, u.username, u.full_name 
     FROM diagnoses d
     JOIN users u ON d.user_id = u.user_id
     ORDER BY d.diagnosis_date DESC
     LIMIT 10"
);

// Get damage statistics
$damageStats = $db->fetchAll(
    "SELECT d.damage_name, COUNT(*) as total
     FROM diagnosis_results dr
     JOIN damages d ON dr.damage_id = d.damage_id
     WHERE dr.is_primary_result = 1
     GROUP BY dr.damage_id
     ORDER BY total DESC
     LIMIT 5"
);

// Get user statistics
$userStats = [
    'total_users' => $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role='user'")['count'],
    'active_today' => $db->fetchOne("SELECT COUNT(DISTINCT user_id) as count FROM activity_logs WHERE DATE(created_at) = CURDATE()")['count'],
    'new_this_month' => $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE MONTH(created_at) = MONTH(CURDATE())")['count']
];

// System stats
$systemStats = [
    'total_symptoms' => $db->fetchOne("SELECT COUNT(*) as count FROM symptoms")['count'],
    'total_damages' => $db->fetchOne("SELECT COUNT(*) as count FROM damages")['count'],
    'total_rules' => $db->fetchOne("SELECT COUNT(*) as count FROM rules")['count']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #1e40af;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #06b6d4;
            --dark: #0f172a;
        }
        
        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--dark), #1e293b);
            min-height: 100vh;
            color: white;
            position: fixed;
            width: 260px;
            z-index: 1000;
        }
        
        .sidebar-brand {
            padding: 25px 20px;
            font-size: 1.5rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            margin: 0;
        }
        
        .sidebar-menu li a {
            color: #cbd5e1;
            text-decoration: none;
            padding: 14px 20px;
            display: block;
            transition: all 0.3s;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(37, 99, 235, 0.2);
            color: white;
            border-left: 4px solid var(--primary);
            padding-left: 16px;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 30px;
        }
        
        .top-bar {
            background: white;
            padding: 25px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            position: relative;
            overflow: hidden;
            margin-bottom: 25px;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(37,99,235,0.1), transparent);
            border-radius: 0 15px 0 100%;
        }
        
        .stat-icon {
            width: 65px;
            height: 65px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        .stat-icon.primary { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; }
        .stat-icon.success { background: linear-gradient(135deg, var(--success), #059669); color: white; }
        .stat-icon.warning { background: linear-gradient(135deg, var(--warning), #d97706); color: white; }
        .stat-icon.info { background: linear-gradient(135deg, var(--info), #0891b2); color: white; }
        
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 1rem;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .card-header {
            background: white;
            border-bottom: 2px solid #f1f5f9;
            padding: 20px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-cog"></i> Admin Panel
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
            <li><a href="damages.php"><i class="fas fa-tools me-2"></i> Kelola Kerusakan</a></li>
            <li><a href="symptoms.php"><i class="fas fa-clipboard-list me-2"></i> Kelola Gejala</a></li>
            <li><hr style="border-color: rgba(255,255,255,0.1);"></li>
            <li><a href="../index.php"><i class="fas fa-home me-2"></i> User Dashboard</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <h4 class="mb-0">Dashboard Admin</h4>
                <small class="text-muted">Selamat datang, <?= $_SESSION['full_name'] ?></small>
            </div>
            <div>
                <span class="text-muted"><i class="fas fa-calendar"></i> <?= date('d F Y') ?></span>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <div class="stat-value"><?= $stats['total_diagnoses'] ?></div>
                    <div class="stat-label">Total Diagnosa</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?= $userStats['total_users'] ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-value"><?= $systemStats['total_damages'] ?></div>
                    <div class="stat-label">Jenis Kerusakan</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-value"><?= number_format($stats['avg_confidence'] ?? 0, 1) ?>%</div>
                    <div class="stat-label">Rata-rata Akurasi</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Diagnoses -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-clock"></i> Diagnosa Terbaru
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Kendaraan</th>
                                        <th>Gejala</th>
                                        <th>Akurasi</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recentDiagnoses as $diag): ?>
                                    <tr>
                                        <td><?= $diag['full_name'] ?></td>
                                        <td><?= $diag['vehicle_brand'] ?> <?= $diag['vehicle_model'] ?></td>
                                        <td><span class="badge bg-primary"><?= $diag['total_symptoms'] ?></span></td>
                                        <td><span class="badge bg-success"><?= number_format($diag['confidence_percentage'], 1) ?>%</span></td>
                                        <td><?= timeAgo($diag['diagnosis_date']) ?></td>
                                        <td>
                                            <a href="../result.php?id=<?= $diag['diagnosis_id'] ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Damages & System Info -->
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <i class="fas fa-chart-pie"></i> Kerusakan Teratas
                    </div>
                    <div class="card-body">
                        <?php foreach($damageStats as $ds): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <strong><?= $ds['damage_name'] ?></strong>
                            </div>
                            <span class="badge bg-primary"><?= $ds['total'] ?>x</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-database"></i> Statistik Sistem
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Gejala</span>
                                <strong><?= $systemStats['total_symptoms'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Kerusakan</span>
                                <strong><?= $systemStats['total_damages'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Aturan</span>
                                <strong><?= $systemStats['total_rules'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>User Aktif Hari Ini</span>
                                <strong><?= $userStats['active_today'] ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>