<?php
// result.php - Diagnosis Result Page
require_once 'config.php';
require_once 'ForwardChaining.php';

requireLogin();

$diagnosis_id = $_GET['id'] ?? 0;

// Get diagnosis detail
$detail = ForwardChaining::getDiagnosisDetail($diagnosis_id);

if (!$detail || $detail['diagnosis']['user_id'] != $_SESSION['user_id']) {
    redirect('index.php');
}

$diagnosis = $detail['diagnosis'];
$symptoms = $detail['symptoms'];
$results = $detail['results'];
$primaryResult = $results[0] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Diagnosa - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #1e40af;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
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
        
        .result-header {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            padding: 50px;
            border-radius: 20px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        }
        
        .result-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .confidence-badge {
            font-size: 3.5rem;
            font-weight: 700;
            background: rgba(255,255,255,0.2);
            padding: 25px 50px;
            border-radius: 20px;
            display: inline-block;
            margin-top: 20px;
            backdrop-filter: blur(10px);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px 25px;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .damage-card {
            border-left: 6px solid var(--success);
            transition: all 0.3s;
        }
        
        .damage-card:hover {
            transform: translateX(8px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .damage-card.primary {
            border-left-width: 10px;
            background: linear-gradient(to right, rgba(16, 185, 129, 0.05), white);
        }
        
        .severity-badge {
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .severity-low { background: #d1fae5; color: #065f46; }
        .severity-medium { background: #fef3c7; color: #92400e; }
        .severity-high { background: #fee2e2; color: #991b1b; }
        .severity-critical { background: #fecaca; color: #7f1d1d; }
        
        .symptom-badge {
            background: #eff6ff;
            color: var(--primary);
            padding: 10px 18px;
            border-radius: 25px;
            margin: 6px;
            display: inline-block;
            font-size: 0.95rem;
            border: 2px solid #dbeafe;
        }
        
        .solution-box {
            background: linear-gradient(to right, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            border-left: 5px solid var(--success);
            padding: 25px;
            border-radius: 12px;
            margin-top: 20px;
        }
        
        .cost-box {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(245, 158, 11, 0.2);
        }
        
        .cost-box h4 {
            color: #92400e;
            font-weight: 700;
            font-size: 1.8rem;
        }
        
        .alternative-result {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .alternative-result:hover {
            border-color: var(--primary);
            background: #eff6ff;
        }
        
        .info-item {
            padding: 15px;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .card { box-shadow: none; border: 1px solid #ddd; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark no-print">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> Car Diagnostic
            </a>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-light me-2">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <button onclick="window.print()" class="btn btn-light">
                    <i class="fas fa-print"></i> Cetak
                </button>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Result Header -->
        <div class="result-header">
            <i class="fas fa-check-circle fa-3x mb-3"></i>
            <h1>Diagnosa Selesai!</h1>
            <p class="lead mb-0">Berikut hasil analisis kerusakan mobil Anda</p>
            <?php if($primaryResult): ?>
                <div class="confidence-badge">
                    <i class="fas fa-chart-line"></i> 
                    <?= number_format($primaryResult['confidence_score'], 1) ?>% Akurasi
                </div>
            <?php endif; ?>
        </div>

        <!-- Vehicle Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-item">
                            <strong><i class="fas fa-car"></i> Kendaraan</strong>
                            <p class="mb-0 mt-2"><?= $diagnosis['vehicle_brand'] ?> <?= $diagnosis['vehicle_model'] ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <strong><i class="fas fa-calendar"></i> Tahun</strong>
                            <p class="mb-0 mt-2"><?= $diagnosis['vehicle_year'] ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <strong><i class="fas fa-clock"></i> Tanggal</strong>
                            <p class="mb-0 mt-2"><?= date('d M Y H:i', strtotime($diagnosis['diagnosis_date'])) ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-item">
                            <strong><i class="fas fa-user"></i> Pengguna</strong>
                            <p class="mb-0 mt-2"><?= $diagnosis['full_name'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Primary Result -->
        <?php if($primaryResult): ?>
        <div class="card damage-card primary mb-4">
            <div class="card-header">
                <i class="fas fa-star"></i> Hasil Diagnosa Utama
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="mb-3">
                            <i class="fas fa-tools"></i> <?= $primaryResult['damage_code'] ?>: <?= $primaryResult['damage_name'] ?>
                        </h3>
                        
                        <div class="mb-4">
                            <span class="severity-badge severity-<?= $primaryResult['severity_level'] ?>">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <?= strtoupper($primaryResult['severity_level']) ?>
                            </span>
                            <span class="badge bg-primary ms-2 fs-6">
                                <i class="fas fa-folder"></i> <?= $primaryResult['category_name'] ?>
                            </span>
                            <span class="badge bg-success ms-2 fs-6">
                                <i class="fas fa-check"></i> <?= $primaryResult['matched_symptoms'] ?>/<?= $primaryResult['total_symptoms_required'] ?> Gejala Cocok
                            </span>
                        </div>
                        
                        <h5 class="mt-4"><i class="fas fa-info-circle text-primary"></i> Deskripsi:</h5>
                        <p class="lead"><?= nl2br($primaryResult['description']) ?></p>
                        
                        <div class="solution-box">
                            <h5><i class="fas fa-tools text-success"></i> Solusi & Rekomendasi:</h5>
                            <p class="mb-0 mt-3"><?= nl2br($primaryResult['solution']) ?></p>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="cost-box">
                            <h5><i class="fas fa-money-bill-wave"></i> Estimasi Biaya</h5>
                            <h4 class="my-3"><?= formatRupiah($primaryResult['estimated_cost_min']) ?></h4>
                            <p class="mb-2">sampai dengan</p>
                            <h4 class="mb-3"><?= formatRupiah($primaryResult['estimated_cost_max']) ?></h4>
                            <small class="text-muted">*Harga dapat bervariasi tergantung lokasi</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Alternative Results -->
        <?php if(count($results) > 1): ?>
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> Kemungkinan Kerusakan Lainnya
            </div>
            <div class="card-body">
                <?php foreach(array_slice($results, 1) as $result): ?>
                <div class="alternative-result">
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            <h5><i class="fas fa-wrench"></i> <?= $result['damage_code'] ?>: <?= $result['damage_name'] ?></h5>
                            <p class="text-muted mb-3"><?= $result['description'] ?></p>
                            <span class="severity-badge severity-<?= $result['severity_level'] ?>">
                                <?= strtoupper($result['severity_level']) ?>
                            </span>
                            <span class="badge bg-secondary ms-2">
                                <?= $result['matched_symptoms'] ?>/<?= $result['total_symptoms_required'] ?> Gejala
                            </span>
                        </div>
                        <div class="col-md-3 text-end">
                            <h3 class="text-primary mb-1"><?= number_format($result['confidence_score'], 1) ?>%</h3>
                            <small class="text-muted">Tingkat Akurasi</small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Symptoms Selected -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clipboard-list"></i> Gejala yang Dialami (<?= count($symptoms) ?>)
            </div>
            <div class="card-body">
                <?php foreach($symptoms as $symptom): ?>
                    <span class="symptom-badge">
                        <i class="fas fa-check-circle"></i> <?= $symptom['symptom_code'] ?>: <?= $symptom['symptom_name'] ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="text-center mt-4 no-print">
            <a href="diagnose.php" class="btn btn-primary btn-lg me-2">
                <i class="fas fa-redo"></i> Diagnosa Lagi
            </a>
            <a href="history.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-history"></i> Lihat Riwayat
            </a>
        </div>

        <!-- Disclaimer -->
        <div class="alert alert-warning mt-4">
            <h6><i class="fas fa-exclamation-triangle"></i> Disclaimer:</h6>
            <small>
                Hasil diagnosa ini bersifat informatif dan berdasarkan sistem pakar dengan metode Forward Chaining. 
                Untuk penanganan lebih lanjut, kami sangat menyarankan untuk konsultasi dengan mekanik profesional. 
                Estimasi biaya yang ditampilkan dapat bervariasi tergantung kondisi kendaraan, suku cadang yang digunakan, dan lokasi bengkel.
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>