<?php
// api/delete_bmi.php - Delete BMI record from database

// Include database configuration
require_once '../config.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse('error', 'Invalid request method');
}

// Get record ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// Validate ID
if (!$id) {
    jsonResponse('error', 'Invalid record ID');
}

try {
    // Create database connection
    $conn = createConnection();
    
    // Prepare SQL statement
    $stmt = $conn->prepare("DELETE FROM bmi_records WHERE id = :id");
    
    // Bind parameter
    $stmt->bindParam(':id', $id);
    
    // Execute statement
    $stmt->execute();
    
    // Check if record was deleted
    if ($stmt->rowCount() > 0) {
        jsonResponse('success', 'Record deleted successfully');
    } else {
        jsonResponse('error', 'Record not found');
    }
    
} catch(PDOException $e) {
    jsonResponse('error', 'Database error: ' . $e->getMessage());
}