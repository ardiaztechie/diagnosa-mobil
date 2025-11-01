<?php
// history.php - Diagnosis History Page
require_once 'config.php';

requireLogin();

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total records
$total = $db->fetchOne(
    "SELECT COUNT(*) as count FROM diagnoses WHERE user_id = ?",
    [$user_id]
)['count'];

$totalPages = ceil($total / $limit);

// Get diagnosis history
$histories = $db->fetchAll(
    "SELECT d.*, 
            (SELECT damage_name FROM diagnosis_results dr 
             JOIN damages dam ON dr.damage_id = dam.damage_id 
             WHERE dr.diagnosis_id = d.diagnosis_id 
             AND dr.is_primary_result = 1 LIMIT 1) as primary_damage
     FROM diagnoses d
     WHERE d.user_id = ?
     ORDER BY d.diagnosis_date DESC
     LIMIT ? OFFSET ?",
    [$user_id, $limit, $offset]
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Diagnosa - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #1e40af;
        }
        
        body {
            background: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand, .nav-link {
            color: white !important;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 50px;
            border-radius: 20px;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .page-header i {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .history-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border-left: 6px solid var(--primary);
        }
        
        .history-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .confidence-circle {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.3rem;
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #64748b;
        }
        
        .empty-state i {
            font-size: 6rem;
            margin-bottom: 30px;
            opacity: 0.3;
        }
        
        .pagination {
            justify-content: center;
            margin-top: 30px;
        }
        
        .page-link {
            border-radius: 10px;
            margin: 0 5px;
            border: 2px solid #e2e8f0;
            color: var(--primary);
        }
        
        .page-link:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-color: var(--primary);
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
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="diagnose.php">
                            <i class="fas fa-stethoscope"></i> Diagnosa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="history.php">
                            <i class="fas fa-history"></i> Riwayat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Page Header -->
        <div class="page-header">
            <i class="fas fa-history"></i>
            <h1>Riwayat Diagnosa</h1>
            <p class="lead mb-0">Total <?= $total ?> diagnosa yang telah dilakukan</p>
        </div>

        <?php if(empty($histories)): ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>Belum Ada Riwayat</h3>
                <p class="mb-4">Anda belum melakukan diagnosa. Mulai diagnosa pertama Anda sekarang!</p>
                <a href="diagnose.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-stethoscope"></i> Mulai Diagnosa
                </a>
            </div>
        <?php else: ?>
            <!-- History List -->
            <?php foreach($histories as $history): ?>
            <div class="history-card">
                <div class="row align-items-center">
                    <div class="col-md-1 text-center mb-3 mb-md-0">
                        <div class="confidence-circle">
                            <?= number_format($history['confidence_percentage'], 0) ?>%
                        </div>
                    </div>
                    
                    <div class="col-md-8 mb-3 mb-md-0">
                        <h5 class="mb-2">
                            <i class="fas fa-car text-primary"></i> 
                            <?= $history['vehicle_brand'] ?> <?= $history['vehicle_model'] ?> 
                            (<?= $history['vehicle_year'] ?>)
                        </h5>
                        <p class="mb-2">
                            <strong><i class="fas fa-tools text-danger"></i> Diagnosa:</strong> 
                            <?= $history['primary_damage'] ?? 'N/A' ?>
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-calendar"></i> <?= date('d M Y H:i', strtotime($history['diagnosis_date'])) ?>
                            &nbsp;|&nbsp;
                            <i class="fas fa-clipboard-check"></i> <?= $history['total_symptoms'] ?> Gejala
                            &nbsp;|&nbsp;
                            <span class="badge bg-<?= $history['status'] === 'completed' ? 'success' : 'warning' ?>">
                                <?= ucfirst($history['status']) ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="col-md-3 text-end">
                        <a href="result.php?id=<?= $history['diagnosis_id'] ?>" 
                           class="btn btn-primary mb-2 w-100">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </a>
                        <button onclick="deleteHistory(<?= $history['diagnosis_id'] ?>)" 
                                class="btn btn-outline-danger btn-sm w-100">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
            <nav>
                <ul class="pagination">
                    <?php if($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">
                            <i class="fas fa-chevron-left"></i> Previous
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">
                            Next <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteHistory(id) {
            if(confirm('Yakin ingin menghapus riwayat diagnosa ini?')) {
                window.location.href = 'delete_history.php?id=' + id;
            }
        }
    </script>
</body>
</html>