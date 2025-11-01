<?php
require_once 'config.php';
$db = Database::getInstance();

// Tes koneksi
$conn = $db->getConnection();
echo "<h3>Koneksi Berhasil</h3>";

// Cek tabel
$tables = $db->fetchAll("SHOW TABLES");
echo "<pre>";
print_r($tables);
echo "</pre>";

// Cek isi tabel gejala
$data = $db->fetchAll("SELECT * FROM symptoms LIMIT 5");
echo "<h3>Contoh data symptoms:</h3>";
print_r($data);
