<?php
// admin/symptoms.php - Manage Symptoms
require_once '../config.php';
requireAdmin();

$db = Database::getInstance();

// Handle CRUD
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['add_symptom'])) {
        $sql = "INSERT INTO symptoms (symptom_code, symptom_name, symptom_question, category) 
                VALUES (?, ?, ?, ?)";
        $db->query($sql, [
            sanitize($_POST['symptom_code']),
            sanitize($_POST['symptom_name']),
            sanitize($_POST['symptom_question']),
            sanitize($_POST['category'])
        ]);
        $_SESSION['success'] = 'Gejala berhasil ditambahkan';
        redirect('symptoms.php');
    }
    
    if(isset($_POST['edit_symptom'])) {
        $sql = "UPDATE symptoms SET symptom_code=?, symptom_name=?, symptom_question=?, category=? 
                WHERE symptom_id=?";
        $db->query($sql, [
            sanitize($_POST['symptom_code']),
            sanitize($_POST['symptom_name']),
            sanitize($_POST['symptom_question']),
            sanitize($_POST['category']),
            $_POST['symptom_id']
        ]);
        $_SESSION['success'] = 'Gejala berhasil diupdate';
        redirect('symptoms.php');
    }
}

if(isset($_GET['delete'])) {
    $db->query("DELETE FROM symptoms WHERE symptom_id = ?", [$_GET['delete']]);
    $_SESSION['success'] = 'Gejala berhasil dihapus';
    redirect('symptoms.php');
}

$symptoms = $db->fetchAll("SELECT * FROM symptoms ORDER BY symptom_code");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Gejala - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #2563eb; --dark: #0f172a; }
        body { background: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; }
        .sidebar { background: linear-gradient(180deg, var(--dark), #1e293b); min-height: 100vh; color: white; position: fixed; width: 260px; }
        .sidebar-brand { padding: 25px 20px; font-size: 1.5rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-menu { list-style: none; padding: 20px 0; margin: 0; }
        .sidebar-menu li a { color: #cbd5e1; text-decoration: none; padding: 14px 20px; display: block; transition: all 0.3s; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background: rgba(37, 99, 235, 0.2); color: white; border-left: 4px solid var(--primary); padding-left: 16px; }
        .main-content { margin-left: 260px; padding: 30px; }
        .top-bar { background: white; padding: 25px 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .table thead th { border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; padding: 15px; }
        .table tbody td { padding: 15px; vertical-align: middle; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand"><i class="fas fa-cog"></i> Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
            <li><a href="damages.php"><i class="fas fa-tools me-2"></i> Kelola Kerusakan</a></li>
            <li><a href="symptoms.php" class="active"><i class="fas fa-clipboard-list me-2"></i> Kelola Gejala</a></li>
            <li><hr style="border-color: rgba(255,255,255,0.1);"></li>
            <li><a href="../index.php"><i class="fas fa-home me-2"></i> User Dashboard</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h4 class="mb-0"><i class="fas fa-clipboard-list"></i> Kelola Gejala</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Tambah Gejala
            </button>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="10%">Kode</th>
                                <th width="25%">Nama Gejala</th>
                                <th width="35%">Pertanyaan</th>
                                <th width="15%">Kategori</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($symptoms as $symptom): ?>
                            <tr>
                                <td><strong><?= $symptom['symptom_code'] ?></strong></td>
                                <td><?= $symptom['symptom_name'] ?></td>
                                <td><?= $symptom['symptom_question'] ?></td>
                                <td><span class="badge bg-info"><?= $symptom['category'] ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick='editSymptom(<?= json_encode($symptom) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?= $symptom['symptom_id'] ?>" class="btn btn-sm btn-danger"
                                       onclick="return confirm('Yakin hapus gejala ini?')">
                                        <i class="fas fa-trash"></i>
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

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Gejala Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kode Gejala</label>
                            <input type="text" name="symptom_code" class="form-control" required placeholder="S001">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Gejala</label>
                            <input type="text" name="symptom_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pertanyaan Diagnosa</label>
                            <textarea name="symptom_question" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="category" class="form-control" required placeholder="Engine Performance">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_symptom" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Gejala</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="symptom_id" id="edit_symptom_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kode Gejala</label>
                            <input type="text" name="symptom_code" id="edit_symptom_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Gejala</label>
                            <input type="text" name="symptom_name" id="edit_symptom_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pertanyaan Diagnosa</label>
                            <textarea name="symptom_question" id="edit_symptom_question" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" name="category" id="edit_category" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_symptom" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editSymptom(symptom) {
            document.getElementById('edit_symptom_id').value = symptom.symptom_id;
            document.getElementById('edit_symptom_code').value = symptom.symptom_code;
            document.getElementById('edit_symptom_name').value = symptom.symptom_name;
            document.getElementById('edit_symptom_question').value = symptom.symptom_question;
            document.getElementById('edit_category').value = symptom.category;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>