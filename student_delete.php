<?php
// student_delete.php - Remove Student Record
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($student_id > 0) {
    // 1. Fetch record to check for photo
    $stmt = $pdo->prepare("SELECT photo FROM students WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $student_id]);
    $student = $stmt->fetch();

    if ($student) {
        // Delete photo file if present
        if (!empty($student['photo']) && file_exists(__DIR__ . '/' . $student['photo'])) {
            @unlink(__DIR__ . '/' . $student['photo']);
        }

        // Delete from database
        $delStmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
        $delStmt->execute([':id' => $student_id]);
    }
}

header("Location: students.php?msg=deleted");
exit();
?>
