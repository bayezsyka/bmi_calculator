<?php
// Aktifkan error log (debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// api/save_bmi.php - Save BMI record to database

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Invalid request method');
}

// Konsisten: nama field dari POST harus sesuai dengan JS (jenis_kelamin)
$nama         = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING);
$usia         = filter_input(INPUT_POST, 'usia', FILTER_VALIDATE_INT);
$jenis_kelamin= filter_input(INPUT_POST, 'jenis_kelamin', FILTER_SANITIZE_STRING);
$berat        = filter_input(INPUT_POST, 'berat', FILTER_VALIDATE_FLOAT);
$tinggi       = filter_input(INPUT_POST, 'tinggi', FILTER_VALIDATE_FLOAT);
$bmi          = filter_input(INPUT_POST, 'bmi', FILTER_VALIDATE_FLOAT);
$kategori     = filter_input(INPUT_POST, 'kategori', FILTER_SANITIZE_STRING);

// Validasi Nama (huruf dan spasi minimal 3)
if (!$nama || !preg_match('/^[a-zA-Z\s]{3,50}$/u', $nama)) {
    jsonResponse('error', 'Nama harus huruf minimal 3 karakter');
}

// Validasi lain
if (!$usia || !$jenis_kelamin || !$berat || !$tinggi || !$bmi || !$kategori) {
    jsonResponse('error', 'Semua field harus diisi!');
}

if ($usia < 1 || $usia > 120) {
    jsonResponse('error', 'Usia harus antara 1-120 tahun');
}
if ($berat < 20 || $berat > 300) {
    jsonResponse('error', 'Berat badan harus antara 20-300 kg');
}
if ($tinggi < 50 || $tinggi > 250) {
    jsonResponse('error', 'Tinggi badan harus antara 50-250 cm');
}

try {
    $conn = createConnection();
    $stmt = $conn->prepare("INSERT INTO bmi_records (nama, usia, jenis_kelamin, berat, tinggi, bmi, kategori) 
                            VALUES (:nama, :usia, :jenis_kelamin, :berat, :tinggi, :bmi, :kategori)");
    $stmt->bindParam(':nama', $nama);
    $stmt->bindParam(':usia', $usia);
    $stmt->bindParam(':jenis_kelamin', $jenis_kelamin);
    $stmt->bindParam(':berat', $berat);
    $stmt->bindParam(':tinggi', $tinggi);
    $stmt->bindParam(':bmi', $bmi);
    $stmt->bindParam(':kategori', $kategori);

    $stmt->execute();
    jsonResponse('success', 'Data BMI berhasil disimpan', ['id' => $conn->lastInsertId()]);
} catch(PDOException $e) {
    jsonResponse('error', 'Database error: ' . $e->getMessage());
}
