<?php
// dashboard.php - Main Administrator Dashboard
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$page_title = 'Dashboard';

// 1. Fetch Key Metrics
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$active_students = $pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Active'")->fetchColumn();
$total_depts = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();

// 2. Department-wise Student Count
$dept_stats = $pdo->query("
    SELECT d.dept_code, d.dept_name, COUNT(s.id) as student_count 
    FROM departments d
    LEFT JOIN students s ON d.id = s.dept_id
    GROUP BY d.id
    ORDER BY student_count DESC
")->fetchAll();

// 3. Recent 5 Students
$recent_stmt = $pdo->query("
    SELECT s.*, d.dept_code, c.course_name 
    FROM students s
    LEFT JOIN departments d ON s.dept_id = d.id
    LEFT JOIN courses c ON s.course_id = c.id
    ORDER BY s.id DESC 
    LIMIT 5
");
$recent_students = $recent_stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
    <!-- Top Navbar -->
    <header class="top-navbar">
        <div class="d-flex align-items-center gap-3">
            <h1 class="page-title">Dashboard Overview</h1>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill">
                <i class="fa-regular fa-calendar me-2 text-primary"></i> <?php echo date('l, d M Y'); ?>
            </span>
            <a href="student_add.php" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-plus me-1"></i> Add Student
            </a>
        </div>
    </header>

    <div class="p-4">
        <!-- Welcome Hero Banner -->
        <div class="card border-0 mb-4 text-white" style="background: linear-gradient(135deg, #3730a3, #4f46e5); border-radius: var(--radius); box-shadow: var(--shadow-md);">
            <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h3 class="fw-bold mb-1">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_fullname']); ?>! 👋</h3>
                    <p class="mb-0 text-white-50">Here is the latest summary of your student records and academic performance.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="students.php" class="btn btn-light fw-semibold text-primary rounded-pill px-3">
                        <i class="fa-solid fa-users me-1"></i> View Directory
                    </a>
                    <a href="export.php" class="btn btn-outline-light rounded-pill px-3">
                        <i class="fa-solid fa-download me-1"></i> Export Data
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 Key Stat Cards -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Total Students</div>
                            <div class="stat-value mt-1"><?php echo number_format($total_students); ?></div>
                        </div>
                        <div class="stat-icon primary">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Active Enrollments</div>
                            <div class="stat-value mt-1"><?php echo number_format($active_students); ?></div>
                        </div>
                        <div class="stat-icon success">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Departments</div>
                            <div class="stat-value mt-1"><?php echo number_format($total_depts); ?></div>
                        </div>
                        <div class="stat-icon warning">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Total Courses</div>
                            <div class="stat-value mt-1"><?php echo number_format($total_courses); ?></div>
                        </div>
                        <div class="stat-icon info">
                            <i class="fa-solid fa-book-bookmark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Students Table -->
            <div class="col-lg-8">
                <div class="custom-card">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Recently Added Students
                        </h5>
                        <a href="students.php" class="btn btn-sm btn-light border text-primary fw-semibold rounded-pill px-3">
                            View All (<?php echo $total_students; ?>)
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Roll No</th>
                                    <th>Course & Dept</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_students)): ?>
                                    <?php foreach ($recent_students as $student): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <?php if (!empty($student['photo']) && file_exists(__DIR__ . '/' . $student['photo'])): ?>
                                                        <img src="<?php echo htmlspecialchars($student['photo']); ?>" class="avatar" alt="photo">
                                                    <?php else: ?>
                                                        <div class="avatar">
                                                            <?php echo strtoupper(substr($student['full_name'], 0, 1)); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($student['full_name']); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($student['roll_no']); ?></span></td>
                                            <td>
                                                <div class="fw-medium text-dark"><?php echo htmlspecialchars($student['course_name'] ?? 'N/A'); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($student['dept_code'] ?? ''); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($student['status'] === 'Active'): ?>
                                                    <span class="badge-soft-success"><?php echo $student['status']; ?></span>
                                                <?php elseif ($student['status'] === 'Graduated'): ?>
                                                    <span class="badge-soft-primary"><?php echo $student['status']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge-soft-warning"><?php echo $student['status']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="student_view.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-light border" title="View Profile">
                                                    <i class="fa-solid fa-eye text-primary"></i>
                                                </a>
                                                <a href="student_edit.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-light border" title="Edit">
                                                    <i class="fa-solid fa-pen text-secondary"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No student records found. Click 'Add Student' to get started!</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Department Distribution Overview -->
            <div class="col-lg-4">
                <div class="custom-card p-4">
                    <h5 class="fw-bold mb-3 text-dark">
                        <i class="fa-solid fa-chart-simple me-2 text-primary"></i> Students by Department
                    </h5>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($dept_stats as $dept): ?>
                            <?php 
                                $percent = ($total_students > 0) ? round(($dept['student_count'] / $total_students) * 100) : 0; 
                            ?>
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-dark" style="font-size: 0.88rem;"><?php echo htmlspecialchars($dept['dept_name']); ?></span>
                                    <span class="badge bg-light text-dark border"><?php echo $dept['student_count']; ?> students</span>
                                </div>
                                <div class="progress" style="height: 7px; border-radius: 10px; background-color: #f1f5f9;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $percent; ?>%;" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr class="my-4">

                    <div class="p-3 bg-light rounded-3">
                        <h6 class="fw-bold mb-2 text-dark"><i class="fa-solid fa-lightbulb text-warning me-2"></i> Quick Tip for Presentation:</h6>
                        <p class="text-muted mb-0" style="font-size: 0.82rem;">
                            Show the real-time department statistics to teachers to demonstrate how relational foreign keys link students with departments automatically!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
