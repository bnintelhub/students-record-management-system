<?php
// student_add.php - Add New Student Record
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$page_title = 'Add New Student';

$errors = [];
$departments = $pdo->query("SELECT * FROM departments ORDER BY dept_name ASC")->fetchAll();
$courses = $pdo->query("SELECT * FROM courses ORDER BY course_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_no        = trim($_POST['roll_no'] ?? '');
    $full_name      = trim($_POST['full_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $phone          = trim($_POST['phone'] ?? '');
    $dob            = trim($_POST['dob'] ?? '');
    $gender         = trim($_POST['gender'] ?? 'Male');
    $dept_id        = (int)($_POST['dept_id'] ?? 0);
    $course_id      = (int)($_POST['course_id'] ?? 0);
    $semester       = trim($_POST['semester'] ?? 'Semester 1');
    $admission_year = (int)($_POST['admission_year'] ?? date('Y'));
    $address        = trim($_POST['address'] ?? '');
    $status         = trim($_POST['status'] ?? 'Active');

    // Validation
    if (empty($roll_no)) $errors[] = "Roll number is required.";
    if (empty($full_name)) $errors[] = "Student's full name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email address is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($dob)) $errors[] = "Date of birth is required.";
    if (empty($dept_id)) $errors[] = "Please select a department.";
    if (empty($course_id)) $errors[] = "Please select a course.";

    // Check unique constraints
    if (empty($errors)) {
        $checkStmt = $pdo->prepare("SELECT id FROM students WHERE roll_no = :roll_no OR email = :email LIMIT 1");
        $checkStmt->execute([':roll_no' => $roll_no, ':email' => $email]);
        if ($checkStmt->fetch()) {
            $errors[] = "A student with this Roll Number or Email already exists.";
        }
    }

    // Photo Upload Handling
    $photo_path = null;
    if (empty($errors) && isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExt, $allowed)) {
            $newFileName = 'student_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
            $destination = __DIR__ . '/assets/uploads/' . $newFileName;
            if (move_uploaded_file($fileTmp, $destination)) {
                $photo_path = 'assets/uploads/' . $newFileName;
            } else {
                $errors[] = "Failed to upload student photo. Please check folder permissions.";
            }
        } else {
            $errors[] = "Invalid photo format. Only JPG, PNG, and WEBP images are allowed.";
        }
    }

    // Insert to DB
    if (empty($errors)) {
        $insertSql = "
            INSERT INTO students 
            (roll_no, full_name, email, phone, dob, gender, dept_id, course_id, semester, admission_year, photo, address, status)
            VALUES 
            (:roll_no, :full_name, :email, :phone, :dob, :gender, :dept_id, :course_id, :semester, :admission_year, :photo, :address, :status)
        ";
        $insertStmt = $pdo->prepare($insertSql);
        $success = $insertStmt->execute([
            ':roll_no'        => $roll_no,
            ':full_name'      => $full_name,
            ':email'          => $email,
            ':phone'          => $phone,
            ':dob'            => $dob,
            ':gender'         => $gender,
            ':dept_id'        => $dept_id,
            ':course_id'      => $course_id,
            ':semester'       => $semester,
            ':admission_year' => $admission_year,
            ':photo'          => $photo_path,
            ':address'        => $address,
            ':status'         => $status
        ]);

        if ($success) {
            header("Location: students.php?msg=added");
            exit();
        } else {
            $errors[] = "Failed to save record to the database.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
    <header class="top-navbar">
        <h1 class="page-title">Add New Student</h1>
        <a href="students.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Directory
        </a>
    </header>

    <div class="p-4">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger rounded-3 mb-4">
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Please fix the following errors:</h6>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="custom-card p-4">
            <form method="POST" action="student_add.php" enctype="multipart/form-data">
                <div class="row g-4">
                    <!-- Section 1: Academic Information -->
                    <div class="col-12">
                        <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-graduation-cap me-2"></i> Academic Details
                        </h5>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Roll Number <span class="text-danger">*</span></label>
                        <input type="text" name="roll_no" class="form-control" placeholder="e.g. CS202401" value="<?php echo htmlspecialchars($_POST['roll_no'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Department / Branch <span class="text-danger">*</span></label>
                        <select name="dept_id" class="form-select" required>
                            <option value="">-- Select Department --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?php echo $d['id']; ?>" <?php echo (isset($_POST['dept_id']) && $_POST['dept_id'] == $d['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['dept_name']) . " (" . $d['dept_code'] . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Enrolled Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-select" required>
                            <option value="">-- Select Course --</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo (isset($_POST['course_id']) && $_POST['course_id'] == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['course_name']) . " (" . $c['course_code'] . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Current Semester</label>
                        <select name="semester" class="form-select">
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="Semester <?php echo $i; ?>" <?php echo (isset($_POST['semester']) && $_POST['semester'] == "Semester $i") ? 'selected' : ''; ?>>
                                    Semester <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Admission Year</label>
                        <input type="number" name="admission_year" class="form-control" min="2000" max="2099" value="<?php echo htmlspecialchars($_POST['admission_year'] ?? date('Y')); ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Graduated">Graduated</option>
                        </select>
                    </div>

                    <!-- Section 2: Personal Information -->
                    <div class="col-12 mt-4">
                        <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-user me-2"></i> Personal Details
                        </h5>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" placeholder="Enter full name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="student@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 9876543210" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Residential Address</label>
                        <textarea name="address" rows="3" class="form-control" placeholder="House no, Street, City, State, PIN..."><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Profile Photo</label>
                        <input type="file" name="photo" id="photoInput" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">JPG, PNG, or WEBP (Max 2MB)</div>
                        <div class="mt-2">
                            <img id="photoPreview" src="#" alt="Preview" style="display:none; width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 2px solid #e2e8f0;">
                        </div>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top d-flex gap-3">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                            <i class="fa-solid fa-check me-2"></i> Save Student Record
                        </button>
                        <a href="students.php" class="btn btn-light border px-4 py-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
