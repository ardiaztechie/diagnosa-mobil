<?php
// diagnose.php - Diagnosis Process Page
require_once 'config.php';
require_once 'ForwardChaining.php';

requireLogin();

$db = Database::getInstance();

// Get all symptoms grouped by category
$symptomsGrouped = ForwardChaining::getAllSymptomsGrouped();

// Process diagnosis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['diagnose'])) {
    $selected_symptoms = $_POST['symptoms'] ?? [];
    $vehicle_brand = sanitize($_POST['vehicle_brand']);
    $vehicle_model = sanitize($_POST['vehicle_model']);
    $vehicle_year = sanitize($_POST['vehicle_year']);
    
    if (empty($selected_symptoms)) {
        $error = 'Pilih minimal 1 gejala!';
    } else {
        // Run Forward Chaining
        $fc = new ForwardChaining();
        $result = $fc->setSymptoms($selected_symptoms)->diagnose();
        
        if ($result['success']) {
            // Save to database
            $saveResult = $fc->saveToDatabase($_SESSION['user_id'], [
                'brand' => $vehicle_brand,
                'model' => $vehicle_model,
                'year' => $vehicle_year
            ]);
            
            if ($saveResult['success']) {
                redirect('result.php?id=' . $saveResult['diagnosis_id']);
            } else {
                $error = $saveResult['message'];
            }
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnosa Mobil - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #1e40af;
            --success: #10b981;
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
        
        .progress-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .progress {
            height: 30px;
            border-radius: 15px;
            background: #e2e8f0;
        }
        
        .progress-bar {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .step-card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .vehicle-form {
            background: #f8fafc;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border: 2px solid #e2e8f0;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 12px 15px;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .symptom-category {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 30px 0 20px 0;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .symptom-item {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 15px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .symptom-item:hover {
            border-color: var(--primary);
            background: #eff6ff;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.15);
        }
        
        .symptom-item.selected {
            background: #eff6ff;
            border-color: var(--primary);
            border-width: 3px;
        }
        
        .symptom-checkbox {
            width: 22px;
            height: 22px;
            cursor: pointer;
            margin-right: 15px;
        }
        
        .symptom-label {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 1.05rem;
        }
        
        .symptom-question {
            font-size: 0.95rem;
            color: #64748b;
        }
        
        .btn-diagnose-submit {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            border: none;
            padding: 18px 60px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s;
        }
        
        .btn-diagnose-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.4);
        }
        
        .selected-count {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 20px 30px;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.4);
            font-weight: 600;
            font-size: 1.1rem;
            z-index: 1000;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
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
            <div class="ms-auto">
                <a href="index.php" class="btn btn-light me-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="logout.php" class="btn btn-light">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- Progress -->
        <div class="progress-section">
            <h5 class="mb-3"><i class="fas fa-tasks"></i> Proses Diagnosa</h5>
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: 50%">
                    <i class="fas fa-clipboard-list me-2"></i> Step 1 of 2: Pilih Gejala
                </div>
            </div>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" id="diagnosisForm">
            <div class="step-card">
                <h3 class="mb-4">
                    <i class="fas fa-car"></i> Informasi Kendaraan
                </h3>
                
                <div class="vehicle-form">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Merk Mobil *</label>
                            <input type="text" name="vehicle_brand" class="form-control" 
                                   placeholder="Contoh: Toyota" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Model *</label>
                            <input type="text" name="vehicle_model" class="form-control" 
                                   placeholder="Contoh: Avanza" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tahun *</label>
                            <input type="number" name="vehicle_year" class="form-control" 
                                   placeholder="Contoh: 2020" min="1980" max="<?= date('Y') ?>" required>
                        </div>
                    </div>
                </div>

                <h3 class="mb-3 mt-4">
                    <i class="fas fa-check-square"></i> Pilih Gejala yang Dialami
                </h3>
                <p class="text-muted mb-4">Centang semua gejala yang sesuai dengan kondisi mobil Anda</p>

                <?php foreach($symptomsGrouped as $category => $symptoms): ?>
                    <div class="symptom-category">
                        <i class="fas fa-folder-open"></i> <?= $category ?>
                    </div>
                    
                    <?php foreach($symptoms as $symptom): ?>
                        <div class="symptom-item" onclick="toggleSymptom(this)">
                            <div class="d-flex align-items-start">
                                <input type="checkbox" name="symptoms[]" 
                                       value="<?= $symptom['symptom_id'] ?>" 
                                       id="symptom_<?= $symptom['symptom_id'] ?>"
                                       class="symptom-checkbox">
                                <div class="flex-grow-1">
                                    <label for="symptom_<?= $symptom['symptom_id'] ?>" 
                                           class="symptom-label" style="cursor: pointer;">
                                        <strong><?= $symptom['symptom_code'] ?>:</strong> 
                                        <?= $symptom['symptom_name'] ?>
                                    </label>
                                    <div class="symptom-question">
                                        <i class="fas fa-question-circle"></i> 
                                        <?= $symptom['symptom_question'] ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <div class="text-center mt-5">
                    <button type="submit" name="diagnose" class="btn-diagnose-submit">
                        <i class="fas fa-brain"></i> Proses Diagnosa Sekarang
                    </button>
                </div>
            </div>
        </form>

        <!-- Selected Count Badge -->
        <div class="selected-count" id="selectedCount" style="display: none;">
            <i class="fas fa-check-circle"></i> 
            <span id="countNumber">0</span> Gejala Dipilih
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSymptom(element) {
            const checkbox = element.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                element.classList.add('selected');
            } else {
                element.classList.remove('selected');
            }
            
            updateCount();
        }

        function updateCount() {
            const checked = document.querySelectorAll('input[name="symptoms[]"]:checked').length;
            const badge = document.getElementById('selectedCount');
            const countNumber = document.getElementById('countNumber');
            
            countNumber.textContent = checked;
            
            if (checked > 0) {
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }

        // Prevent label double-toggle
        document.querySelectorAll('.symptom-item input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function(e) {
                e.stopPropagation();
                const item = this.closest('.symptom-item');
                if (this.checked) {
                    item.classList.add('selected');
                } else {
                    item.classList.remove('selected');
                }
                updateCount();
            });
        });

        // Form validation
        document.getElementById('diagnosisForm').addEventListener('submit', function(e) {
            const checked = document.querySelectorAll('input[name="symptoms[]"]:checked').length;
            
            if (checked === 0) {
                e.preventDefault();
                alert('Pilih minimal 1 gejala untuk melakukan diagnosa!');
                return false;
            }
            
            // Show loading
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses Diagnosa...';
            btn.disabled = true;
        });

        // Initial count
        updateCount();
    </script>
</body>
</html>