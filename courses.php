<?php
// courses.php - Course & Department Management
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$page_title = 'Courses & Departments';
$msg = '';
$err = '';

// Handle Add Department
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_dept') {
    $dept_name = trim($_POST['dept_name'] ?? '');
    $dept_code = strtoupper(trim($_POST['dept_code'] ?? ''));

    if (!empty($dept_name) && !empty($dept_code)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO departments (dept_name, dept_code) VALUES (:name, :code)");
            $stmt->execute([':name' => $dept_name, ':code' => $dept_code]);
            $msg = "Department added successfully!";
        } catch (PDOException $e) {
            $err = "Error: Department code must be unique.";
        }
    } else {
        $err = "All department fields are required.";
    }
}

// Handle Add Course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_course') {
    $dept_id     = (int)($_POST['dept_id'] ?? 0);
    $course_name = trim($_POST['course_name'] ?? '');
    $course_code = strtoupper(trim($_POST['course_code'] ?? ''));
    $duration    = (int)($_POST['duration_years'] ?? 3);

    if ($dept_id > 0 && !empty($course_name) && !empty($course_code)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO courses (dept_id, course_name, course_code, duration_years) VALUES (:dept, :name, :code, :duration)");
            $stmt->execute([':dept' => $dept_id, ':name' => $course_name, ':code' => $course_code, ':duration' => $duration]);
            $msg = "Course added successfully!";
        } catch (PDOException $e) {
            $err = "Error: Course code must be unique.";
        }
    } else {
        $err = "All course fields are required.";
    }
}

// Fetch Departments with student counts
$depts = $pdo->query("
    SELECT d.*, 
           COUNT(DISTINCT c.id) AS total_courses, 
           COUNT(DISTINCT s.id) AS total_students
    FROM departments d
    LEFT JOIN courses c ON d.id = c.dept_id
    LEFT JOIN students s ON d.id = s.dept_id
    GROUP BY d.id
    ORDER BY d.dept_name ASC
")->fetchAll();

// Fetch Courses with department details
$courses = $pdo->query("
    SELECT c.*, d.dept_name, d.dept_code,
           COUNT(s.id) AS student_count
    FROM courses c
    JOIN departments d ON c.dept_id = d.id
    LEFT JOIN students s ON c.id = s.course_id
    GROUP BY c.id
    ORDER BY d.dept_name ASC, c.course_name ASC
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
    <header class="top-navbar">
        <h1 class="page-title">Departments & Academic Courses</h1>
    </header>

    <div class="p-4">
        <?php if (!empty($msg)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?php echo htmlspecialchars($msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($err)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo htmlspecialchars($err); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Column: Departments -->
            <div class="col-lg-5">
                <div class="custom-card mb-4">
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-building me-2 text-primary"></i> Add New Department</h6>
                    </div>
                    <div class="p-3">
                        <form method="POST" action="courses.php">
                            <input type="hidden" name="action" value="add_dept">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Department Name</label>
                                <input type="text" name="dept_name" class="form-control" placeholder="e.g. Artificial Intelligence & DS" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size: 0.85rem;">Department Code</label>
                                <input type="text" name="dept_code" class="form-control" placeholder="e.g. AI-DS" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm w-100 py-2">
                                <i class="fa-solid fa-plus me-1"></i> Add Department
                            </button>
                        </form>
                    </div>
                </div>

                <div class="custom-card">
                    <div class="p-3 bg-light border-bottom">
                        <h6 class="fw-bold text-dark mb-0">Active Departments (<?php echo count($depts); ?>)</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Department Name</th>
                                    <th class="text-end">Students</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($depts as $d): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($d['dept_code']); ?></span></td>
                                        <td class="fw-semibold text-dark"><?php echo htmlspecialchars($d['dept_name']); ?></td>
                                        <td class="text-end"><span class="badge-soft-primary"><?php echo $d['total_students']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Courses -->
            <div class="col-lg-7">
                <div class="custom-card mb-4">
                    <div class="p-3 bg-light border-bottom">
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-book me-2 text-primary"></i> Add New Course</h6>
                    </div>
                    <div class="p-3">
                        <form method="POST" action="courses.php">
                            <input type="hidden" name="action" value="add_course">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size: 0.85rem;">Select Department</label>
                                    <select name="dept_id" class="form-select" required>
                                        <option value="">-- Choose Dept --</option>
                                        <?php foreach ($depts as $d): ?>
                                            <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['dept_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" style="font-size: 0.85rem;">Course Code</label>
                                    <input type="text" name="course_code" class="form-control" placeholder="e.g. BTECH-AI" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold" style="font-size: 0.85rem;">Course Title / Degree</label>
                                    <input type="text" name="course_name" class="form-control" placeholder="e.g. B.Tech in Artificial Intelligence" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold" style="font-size: 0.85rem;">Duration (Years)</label>
                                    <input type="number" name="duration_years" class="form-control" value="4" min="1" max="6" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-sm py-2 px-4">
                                        <i class="fa-solid fa-plus me-1"></i> Add Course
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="custom-card">
                    <div class="p-3 bg-light border-bottom">
                        <h6 class="fw-bold text-dark mb-0">Enrolled Courses (<?php echo count($courses); ?>)</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Name</th>
                                    <th>Department</th>
                                    <th>Duration</th>
                                    <th class="text-end">Students</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($courses as $c): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($c['course_code']); ?></code></td>
                                        <td class="fw-semibold text-dark"><?php echo htmlspecialchars($c['course_name']); ?></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($c['dept_code']); ?></span></td>
                                        <td><?php echo $c['duration_years']; ?> Yrs</td>
                                        <td class="text-end"><span class="badge-soft-primary"><?php echo $c['student_count']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
