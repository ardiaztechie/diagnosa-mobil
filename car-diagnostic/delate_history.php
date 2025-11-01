<?php
// delete_history.php - Delete Diagnosis History
require_once 'config.php';

requireLogin();

if(isset($_GET['id'])) {
    $diagnosis_id = (int)$_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    $db = Database::getInstance();
    
    // Verify ownership
    $diagnosis = $db->fetchOne(
        "SELECT user_id FROM diagnoses WHERE diagnosis_id = ?",
        [$diagnosis_id]
    );
    
    if($diagnosis && $diagnosis['user_id'] == $user_id) {
        // Delete diagnosis (cascade akan menghapus detail)
        $db->query("DELETE FROM diagnoses WHERE diagnosis_id = ?", [$diagnosis_id]);
        
        logActivity($user_id, 'diagnosis_deleted', "Deleted diagnosis ID: $diagnosis_id");
        
        $_SESSION['message'] = 'Riwayat diagnosa berhasil dihapus';
    }
}

redirect('history.php');
?>