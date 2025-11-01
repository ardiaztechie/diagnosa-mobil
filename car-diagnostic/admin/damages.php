<?php
// admin/damages.php - Manage Damages
require_once '../config.php';
requireAdmin();

$db = Database::getInstance();

// Handle CRUD operations
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['add_damage'])) {
        $sql = "INSERT INTO damages (damage_code, damage_name, category_id, description, solution, 
                estimated_cost_min, estimated_cost_max, severity_level) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $db->query($sql, [
            sanitize($_POST['damage_code']),
            sanitize($_POST['damage_name']),
            $_POST['category_id'],
            sanitize($_POST['description']),
            sanitize($_POST['solution']),
            $_POST['cost_min'],
            $_POST['cost_max'],
            $_POST['severity']
        ]);
        
        $_SESSION['success'] = 'Kerusakan berhasil ditambahkan';
        redirect('damages.php');
    }
    
    if(isset($_POST['edit_damage'])) {
        $sql = "UPDATE damages SET damage_code=?, damage_name=?, category_id=?, description=?, 
                solution=?, estimated_cost_min=?, estimated_cost_max=?, severity_level=? 
                WHERE damage_id=?";
        
        $db->query($sql, [
            sanitize($_POST['damage_code']),
            sanitize($_POST['damage_name']),
            $_POST['category_id'],
            sanitize($_POST['description']),
            sanitize($_POST['solution']),
            $_POST['cost_min'],
            $_POST['cost_max'],
            $_POST['severity'],
            $_POST['damage_id']
        ]);
        
        $_SESSION['success'] = 'Kerusakan berhasil diupdate';
        redirect('damages.php');
    }
}

if(isset($_GET['delete'])) {
    $db->query("DELETE FROM damages WHERE damage_id = ?", [$_GET['delete']]);
    $_SESSION['success'] = 'Kerusakan berhasil dihapus';
    redirect('damages.php');
}

// Get all damages
$damages = $db->fetchAll(
    "SELECT d.*, dc.category_name 
     FROM damages d
     LEFT JOIN damage_categories dc ON d.category_id = dc.category_id
     ORDER BY d.damage_code"
);

// Get categories
$categories = $db->fetchAll("SELECT * FROM damage_categories ORDER BY category_name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kerusakan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #1e40af;
            --dark: #0f172a;
        }
        body { background: #f1f5f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; }
        .sidebar { background: linear-gradient(180deg, var(--dark), #1e293b); min-height: 100vh; color: white; position: fixed; width: 260px; }
        .sidebar-brand { padding: 25px 20px; font-size: 1.5rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-menu { list-style: none; padding: 20px 0; margin: 0; }
        .sidebar-menu li a { color: #cbd5e1; text-decoration: none; padding: 14px 20px; display: block; transition: all 0.3s; }
        .sidebar-menu li a:hover, .sidebar-menu li a.active { background: rgba(37, 99, 235, 0.2); color: white; border-left: 4px solid var(--primary); padding-left: 16px; }
        .main-content { margin-left: 260px; padding: 30px; }
        .top-bar { background: white; padding: 25px 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .table { margin-bottom: 0; }
        .table thead th { border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; padding: 15px; }
        .table tbody td { padding: 15px; vertical-align: middle; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand"><i class="fas fa-cog"></i> Admin Panel</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
            <li><a href="damages.php" class="active"><i class="fas fa-tools me-2"></i> Kelola Kerusakan</a></li>
            <li><a href="symptoms.php"><i class="fas fa-clipboard-list me-2"></i> Kelola Gejala</a></li>
            <li><hr style="border-color: rgba(255,255,255,0.1);"></li>
            <li><a href="../index.php"><i class="fas fa-home me-2"></i> User Dashboard</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <h4 class="mb-0"><i class="fas fa-tools"></i> Kelola Kerusakan</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus"></i> Tambah Kerusakan
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
                                <th width="8%">Kode</th>
                                <th width="20%">Nama Kerusakan</th>
                                <th width="12%">Kategori</th>
                                <th width="20%">Estimasi Biaya</th>
                                <th width="10%">Severity</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($damages as $damage): ?>
                            <tr>
                                <td><strong><?= $damage['damage_code'] ?></strong></td>
                                <td><?= $damage['damage_name'] ?></td>
                                <td><span class="badge bg-info"><?= $damage['category_name'] ?></span></td>
                                <td><?= formatRupiah($damage['estimated_cost_min']) ?> - <?= formatRupiah($damage['estimated_cost_max']) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $damage['severity_level'] === 'critical' ? 'danger' : 
                                        ($damage['severity_level'] === 'high' ? 'warning' : 
                                        ($damage['severity_level'] === 'medium' ? 'info' : 'secondary')) ?>">
                                        <?= strtoupper($damage['severity_level']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick='editDamage(<?= htmlspecialchars(json_encode($damage)) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?= $damage['damage_id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Yakin hapus kerusakan ini?')">
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
                    <h5 class="modal-title">Tambah Kerusakan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Kerusakan</label>
                                <input type="text" name="damage_code" class="form-control" required placeholder="D001">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Kerusakan</label>
                                <input type="text" name="damage_name" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= $cat['category_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Severity</label>
                                <select name="severity" class="form-select" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Solusi</label>
                            <textarea name="solution" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estimasi Biaya Min (Rp)</label>
                                <input type="number" name="cost_min" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estimasi Biaya Max (Rp)</label>
                                <input type="number" name="cost_max" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_damage" class="btn btn-primary">Simpan</button>
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
                    <h5 class="modal-title">Edit Kerusakan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="damage_id" id="edit_damage_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kode Kerusakan</label>
                                <input type="text" name="damage_code" id="edit_damage_code" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Kerusakan</label>
                                <input type="text" name="damage_name" id="edit_damage_name" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" id="edit_category_id" class="form-select" required>
                                    <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= $cat['category_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Severity</label>
                                <select name="severity" id="edit_severity" class="form-select" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" id="edit_description" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Solusi</label>
                            <textarea name="solution" id="edit_solution" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estimasi Biaya Min (Rp)</label>
                                <input type="number" name="cost_min" id="edit_cost_min" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estimasi Biaya Max (Rp)</label>
                                <input type="number" name="cost_max" id="edit_cost_max" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_damage" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editDamage(damage) {
            document.getElementById('edit_damage_id').value = damage.damage_id;
            document.getElementById('edit_damage_code').value = damage.damage_code;
            document.getElementById('edit_damage_name').value = damage.damage_name;
            document.getElementById('edit_category_id').value = damage.category_id;
            document.getElementById('edit_severity').value = damage.severity_level;
            document.getElementById('edit_description').value = damage.description;
            document.getElementById('edit_solution').value = damage.solution;
            document.getElementById('edit_cost_min').value = damage.estimated_cost_min;
            document.getElementById('edit_cost_max').value = damage.estimated_cost_max;
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>