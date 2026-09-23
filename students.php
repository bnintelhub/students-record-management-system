<?php
// students.php - All Students Directory & Management
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$page_title = 'Students Directory';

// Handle Filters
$dept_filter = isset($_GET['department']) ? (int)$_GET['department'] : 0;
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build Query
$sql = "
    SELECT s.*, d.dept_name, d.dept_code, c.course_name, c.course_code 
    FROM students s
    LEFT JOIN departments d ON s.dept_id = d.id
    LEFT JOIN courses c ON s.course_id = c.id
    WHERE 1=1
";
$params = [];

if (!empty($dept_filter)) {
    $sql .= " AND s.dept_id = :dept_id";
    $params[':dept_id'] = $dept_filter;
}

if (!empty($status_filter)) {
    $sql .= " AND s.status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($search_query)) {
    $sql .= " AND (s.full_name LIKE :search OR s.roll_no LIKE :search OR s.email LIKE :search OR s.phone LIKE :search)";
    $params[':search'] = "%$search_query%";
}

$sql .= " ORDER BY s.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Fetch Departments for Filter Dropdown
$departments = $pdo->query("SELECT * FROM departments ORDER BY dept_name ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
    <header class="top-navbar">
        <h1 class="page-title">Student Records</h1>
        <div class="d-flex align-items-center gap-2">
            <a href="export.php<?php echo (!empty($dept_filter)) ? '?department='.$dept_filter : ''; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-file-csv me-1"></i> Export to CSV
            </a>
            <a href="student_add.php" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-plus me-1"></i> Add New Student
            </a>
        </div>
    </header>

    <div class="p-4">
        <!-- Success / Error Notifications -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>
                <?php 
                    if ($_GET['msg'] === 'added') echo "Student record successfully added!";
                    elseif ($_GET['msg'] === 'updated') echo "Student record updated successfully!";
                    elseif ($_GET['msg'] === 'deleted') echo "Student record removed successfully!";
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Card -->
        <div class="custom-card p-3 mb-4">
            <form method="GET" action="students.php" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" id="studentSearchInput" name="search" class="form-control border-start-0" placeholder="Type to search name, roll no, email..." value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="department" class="form-select" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>" <?php echo ($dept_filter == $dept['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['dept_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="Active" <?php echo ($status_filter === 'Active') ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo ($status_filter === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                        <option value="Graduated" <?php echo ($status_filter === 'Graduated') ? 'selected' : ''; ?>>Graduated</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    <?php if (!empty($search_query) || !empty($dept_filter) || !empty($status_filter)): ?>
                        <a href="students.php" class="btn btn-light border" title="Reset Filters"><i class="fa-solid fa-rotate-left"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Student Records Table -->
        <div class="custom-card overflow-hidden">
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">
                    Showing <?php echo count($students); ?> Student(s)
                </span>
                <small class="text-muted"><i class="fa-solid fa-bolt text-warning me-1"></i> Real-time instant search is enabled</small>
            </div>

            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Roll Number</th>
                            <th>Department & Course</th>
                            <th>Semester</th>
                            <th>Phone / Contact</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $row): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <?php if (!empty($row['photo']) && file_exists(__DIR__ . '/' . $row['photo'])): ?>
                                                <img src="<?php echo htmlspecialchars($row['photo']); ?>" class="avatar" alt="Avatar">
                                            <?php else: ?>
                                                <div class="avatar">
                                                    <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['full_name']); ?></div>
                                                <small class="text-muted"><i class="fa-regular fa-envelope me-1"></i><?php echo htmlspecialchars($row['email']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-primary border fw-bold"><?php echo htmlspecialchars($row['roll_no']); ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($row['course_name'] ?? 'N/A'); ?></div>
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.72rem;"><?php echo htmlspecialchars($row['dept_code'] ?? ''); ?></span>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['semester']); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'Active'): ?>
                                            <span class="badge-soft-success">Active</span>
                                        <?php elseif ($row['status'] === 'Graduated'): ?>
                                            <span class="badge-soft-primary">Graduated</span>
                                        <?php else: ?>
                                            <span class="badge-soft-warning">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="student_view.php?id=<?php echo $row['id']; ?>" class="btn btn-light border" title="View Full Profile">
                                                <i class="fa-solid fa-eye text-primary"></i>
                                            </a>
                                            <a href="student_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-light border" title="Edit Student">
                                                <i class="fa-solid fa-pen text-secondary"></i>
                                            </a>
                                            <a href="student_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-light border" title="Delete Student" onclick="return confirmDelete(event, 'Are you sure you want to delete <?php echo addslashes($row['full_name']); ?>?');">
                                                <i class="fa-solid fa-trash text-danger"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-folder-open fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                    No student records found matching your filters.
                                    <div class="mt-2">
                                        <a href="student_add.php" class="btn btn-sm btn-primary rounded-pill px-3">Add Student</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
