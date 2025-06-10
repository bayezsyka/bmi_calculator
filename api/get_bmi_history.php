<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse('error', 'Invalid request method');
}

try {
    $conn = createConnection();
    $stmt = $conn->prepare("SELECT * FROM bmi_records ORDER BY tanggal DESC");
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse('success', '', $records);
} catch(PDOException $e) {
    jsonResponse('error', 'Database error: ' . $e->getMessage());
}
