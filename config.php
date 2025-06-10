<?php
// config.php - Database configuration & helpers

$dbHost = 'localhost';
$dbName = 'bmi_tracker';
$dbUser = 'root';
$dbPass = '';

function createConnection() {
    global $dbHost, $dbName, $dbUser, $dbPass;
    try {
        $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        if ($e->getCode() == 1049) {
            $tempConn = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
            $tempConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $tempConn->exec("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $conn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            createTables($conn);
            return $conn;
        } else {
            jsonResponse('error', 'Database error: ' . $e->getMessage());
        }
    }
}

function createTables($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS bmi_records (
        id INT(11) NOT NULL AUTO_INCREMENT,
        nama VARCHAR(100) NOT NULL,
        usia INT(3) NOT NULL,
        jenis_kelamin VARCHAR(10) NOT NULL,
        berat DECIMAL(5,1) NOT NULL,
        tinggi DECIMAL(5,1) NOT NULL,
        bmi DECIMAL(4,1) NOT NULL,
        kategori VARCHAR(50) NOT NULL,
        tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sql);
}

function jsonResponse($status, $message = '', $data = null) {
    header('Content-Type: application/json');
    $out = ['status' => $status];
    if ($message) $out['message'] = $message;
    if (!is_null($data)) $out['data'] = $data;
    echo json_encode($out);
    exit;
}
