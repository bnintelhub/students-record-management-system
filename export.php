<?php
// export.php - Export Students to CSV/Excel
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$dept_id = isset($_GET['department']) ? (int)$_GET['department'] : 0;

$sql = "
    SELECT s.roll_no, s.full_name, s.email, s.phone, s.dob, s.gender,
           d.dept_name, c.course_name, s.semester, s.admission_year,
           s.status, s.address, s.created_at
    FROM students s
    LEFT JOIN departments d ON s.dept_id = d.id
    LEFT JOIN courses c ON s.course_id = c.id
";

if ($dept_id > 0) {
    $sql .= " WHERE s.dept_id = :dept_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':dept_id' => $dept_id]);
} else {
    $stmt = $pdo->query($sql);
}

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filename
$filename = "student_records_" . date('Ymd_His') . ".csv";

// Output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add BOM for UTF-8 Excel compatibility
fputs($output, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));

// Output the column headings
fputcsv($output, [
    'Roll Number',
    'Full Name',
    'Email Address',
    'Phone',
    'Date of Birth',
    'Gender',
    'Department',
    'Course',
    'Semester',
    'Admission Year',
    'Status',
    'Address',
    'Registration Date'
]);

// Loop over rows, outputting them
foreach ($students as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
