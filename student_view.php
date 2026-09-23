<?php
// student_view.php - Printable Student Record & Profile Sheet
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($student_id <= 0) {
    header("Location: students.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT s.*, d.dept_name, d.dept_code, c.course_name, c.course_code, c.duration_years
    FROM students s
    LEFT JOIN departments d ON s.dept_id = d.id
    LEFT JOIN courses c ON s.course_id = c.id
    WHERE s.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Student not found.");
}

$page_title = 'Student Profile - ' . $student['full_name'];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
    <header class="top-navbar no-print">
        <h1 class="page-title">Student Profile Card</h1>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-print me-1"></i> Print / Save PDF
            </button>
            <a href="student_edit.php?id=<?php echo $student['id']; ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-pen-to-square me-1"></i> Edit Profile
            </a>
            <a href="students.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </header>

    <div class="p-4">
        <!-- Profile Container Card -->
        <div class="custom-card overflow-hidden shadow-sm mx-auto" style="max-width: 900px;">
            <!-- Header Banner -->
            <div class="student-profile-header position-relative">
                <div class="d-flex flex-column flex-sm-row align-items-center gap-4">
                    <?php if (!empty($student['photo']) && file_exists(__DIR__ . '/' . $student['photo'])): ?>
                        <img src="<?php echo htmlspecialchars($student['photo']); ?>" class="profile-avatar" alt="Photo">
                    <?php else: ?>
                        <div class="profile-avatar d-flex align-items-center justify-content-center text-primary fs-1 fw-bold">
                            <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>

                    <div class="text-center text-sm-start flex-grow-1">
                        <div class="d-flex align-items-center justify-content-center justify-content-sm-start gap-2 mb-1">
                            <h3 class="fw-bold mb-0 text-white"><?php echo htmlspecialchars($student['full_name']); ?></h3>
                            <span class="badge bg-white text-primary fw-bold"><?php echo htmlspecialchars($student['status']); ?></span>
                        </div>
                        <p class="text-white-50 mb-2"><?php echo htmlspecialchars($student['course_name']); ?> (<?php echo htmlspecialchars($student['dept_code']); ?>)</p>
                        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-sm-start">
                            <span class="badge bg-white bg-opacity-25 text-white">
                                <i class="fa-solid fa-id-badge me-1"></i> Roll: <?php echo htmlspecialchars($student['roll_no']); ?>
                            </span>
                            <span class="badge bg-white bg-opacity-25 text-white">
                                <i class="fa-solid fa-calendar me-1"></i> <?php echo htmlspecialchars($student['semester']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Body -->
            <div class="p-4">
                <div class="row g-4">
                    <!-- Academic Details Column -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                <i class="fa-solid fa-graduation-cap text-primary me-2"></i> Academic Record
                            </h6>
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Department:</td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($student['dept_name']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Course / Degree:</td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($student['course_name']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Course Code:</td>
                                    <td class="fw-semibold text-dark"><code><?php echo htmlspecialchars($student['course_code']); ?></code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Current Semester:</td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($student['semester']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Batch / Admission:</td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($student['admission_year']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Course Duration:</td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($student['duration_years']); ?> Years</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Personal Information Column -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-light h-100">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                <i class="fa-solid fa-address-card text-primary me-2"></i> Personal Information
                            </h6>
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Email Address:</td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($student['email']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Phone Number:</td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($student['phone']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Gender:</td>
                                    <td class="fw-semibold text-dark"><?php echo htmlspecialchars($student['gender']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Date of Birth:</td>
                                    <td class="fw-semibold text-dark"><?php echo date('d M Y', strtotime($student['dob'])); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Registered On:</td>
                                    <td class="fw-semibold text-dark"><?php echo date('d M Y, h:i A', strtotime($student['created_at'])); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Address Box -->
                    <div class="col-12">
                        <div class="p-3 border rounded-3 bg-light">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">
                                <i class="fa-solid fa-location-dot text-primary me-2"></i> Residential Address
                            </h6>
                            <p class="mb-0 text-dark">
                                <?php echo nl2br(htmlspecialchars($student['address'] ?: 'No address specified.')); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Printable Footer -->
                <div class="mt-4 pt-4 border-top d-flex justify-content-between text-muted" style="font-size: 0.8rem;">
                    <div>Generated from: <b>Student Record Management System</b></div>
                    <div>Print Date: <?php echo date('d-m-Y'); ?> &bull; Authorized College Record</div>
                </div>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
