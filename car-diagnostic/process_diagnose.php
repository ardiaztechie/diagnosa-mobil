<?php
require_once 'config.php';
require_once 'ForwardChaining.php';

// Pastikan user login
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_symptoms = $_POST['symptoms'] ?? [];
    $vehicle_brand = sanitize($_POST['vehicle_brand']);
    $vehicle_model = sanitize($_POST['vehicle_model']);
    $vehicle_year  = sanitize($_POST['vehicle_year']);

    if (empty($selected_symptoms)) {
        $_SESSION['error'] = 'Pilih minimal 1 gejala!';
        redirect('diagnose.php');
    }

    // Jalankan logika forward chaining
    $fc = new ForwardChaining();
    $result = $fc->setSymptoms($selected_symptoms)->diagnose();

    // Jika tidak ada hasil diagnosa
    if (empty($result['damage'])) {
        $_SESSION['error'] = 'Tidak ditemukan hasil diagnosa untuk gejala yang dipilih.';
        redirect('diagnose.php');
    }

    // Simpan hasil diagnosa ke database
    $saveResult = $fc->saveToDatabase($_SESSION['user_id'], [
        'brand' => $vehicle_brand,
        'model' => $vehicle_model,
        'year'  => $vehicle_year,
        'symptoms' => $selected_symptoms,
        'result'   => $result
    ]);

    // Cek hasil penyimpanan
    if ($saveResult['success'] && isset($saveResult['diagnosis_id'])) {
        redirect('result.php?id=' . $saveResult['diagnosis_id']);
    } else {
        $_SESSION['error'] = 'Gagal menyimpan hasil diagnosa.';
        redirect('diagnose.php');
    }
}
?>
