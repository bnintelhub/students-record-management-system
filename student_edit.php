<?php
// student_edit.php - Edit Existing Student Record
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

$page_title = 'Edit Student Record';

$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($student_id <= 0) {
    header("Location: students.php");
    exit();
}

// Fetch existing record
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Student record not found.");
}

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
    $semester       = trim($_POST['semester'] ?? '');
    $admission_year = (int)($_POST['admission_year'] ?? date('Y'));
    $address        = trim($_POST['address'] ?? '');
    $status         = trim($_POST['status'] ?? 'Active');

    // Validation
    if (empty($roll_no)) $errors[] = "Roll number is required.";
    if (empty($full_name)) $errors[] = "Student full name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($dob)) $errors[] = "Date of birth is required.";
    if (empty($dept_id)) $errors[] = "Please select a department.";
    if (empty($course_id)) $errors[] = "Please select a course.";

    // Check unique constraints excluding current student
    if (empty($errors)) {
        $checkStmt = $pdo->prepare("SELECT id FROM students WHERE (roll_no = :roll_no OR email = :email) AND id != :id LIMIT 1");
        $checkStmt->execute([':roll_no' => $roll_no, ':email' => $email, ':id' => $student_id]);
        if ($checkStmt->fetch()) {
            $errors[] = "Another student with this Roll Number or Email already exists.";
        }
    }

    // Photo Upload Handling
    $photo_path = $student['photo'];
    if (empty($errors) && isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExt, $allowed)) {
            $newFileName = 'student_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
            $destination = __DIR__ . '/assets/uploads/' . $newFileName;
            if (move_uploaded_file($fileTmp, $destination)) {
                // Delete previous photo if exists
                if (!empty($student['photo']) && file_exists(__DIR__ . '/' . $student['photo'])) {
                    @unlink(__DIR__ . '/' . $student['photo']);
                }
                $photo_path = 'assets/uploads/' . $newFileName;
            } else {
                $errors[] = "Failed to upload new photo.";
            }
        } else {
            $errors[] = "Invalid photo format. Only JPG, PNG, and WEBP images are allowed.";
        }
    }

    // Update Record
    if (empty($errors)) {
        $updateSql = "
            UPDATE students SET 
                roll_no = :roll_no,
                full_name = :full_name,
                email = :email,
                phone = :phone,
                dob = :dob,
                gender = :gender,
                dept_id = :dept_id,
                course_id = :course_id,
                semester = :semester,
                admission_year = :admission_year,
                photo = :photo,
                address = :address,
                status = :status
            WHERE id = :id
        ";
        $updateStmt = $pdo->prepare($updateSql);
        $success = $updateStmt->execute([
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
            ':status'         => $status,
            ':id'             => $student_id
        ]);

        if ($success) {
            header("Location: students.php?msg=updated");
            exit();
        } else {
            $errors[] = "Failed to update record.";
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="main-content">
    <header class="top-navbar">
        <h1 class="page-title">Edit Student Record</h1>
        <div class="d-flex gap-2">
            <a href="student_view.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-eye me-1"></i> View Profile
            </a>
            <a href="students.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </header>

    <div class="p-4">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger rounded-3 mb-4">
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Errors Encountered:</h6>
                <ul class="mb-0 ps-3">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="custom-card p-4">
            <form method="POST" action="student_edit.php?id=<?php echo $student['id']; ?>" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-12">
                        <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-graduation-cap me-2"></i> Academic Details
                        </h5>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Roll Number <span class="text-danger">*</span></label>
                        <input type="text" name="roll_no" class="form-control" value="<?php echo htmlspecialchars($student['roll_no']); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                        <select name="dept_id" class="form-select" required>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?php echo $d['id']; ?>" <?php echo ($student['dept_id'] == $d['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['dept_name']) . " (" . $d['dept_code'] . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-select" required>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($student['course_id'] == $c['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['course_name']) . " (" . $c['course_code'] . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Current Semester</label>
                        <select name="semester" class="form-select">
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="Semester <?php echo $i; ?>" <?php echo ($student['semester'] === "Semester $i") ? 'selected' : ''; ?>>
                                    Semester <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Admission Year</label>
                        <input type="number" name="admission_year" class="form-control" value="<?php echo htmlspecialchars($student['admission_year']); ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="Active" <?php echo ($student['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo ($student['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="Graduated" <?php echo ($student['status'] === 'Graduated') ? 'selected' : ''; ?>>Graduated</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4">
                        <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-user me-2"></i> Personal Details
                        </h5>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($student['dob']); ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="Male" <?php echo ($student['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($student['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($student['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Address</label>
                        <textarea name="address" rows="3" class="form-control"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Update Photo</label>
                        <input type="file" name="photo" id="photoInput" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <div class="mt-2 d-flex align-items-center gap-3">
                            <?php if (!empty($student['photo']) && file_exists(__DIR__ . '/' . $student['photo'])): ?>
                                <img src="<?php echo htmlspecialchars($student['photo']); ?>" id="photoPreview" alt="Current Photo" style="width: 75px; height: 75px; object-fit: cover; border-radius: 10px; border: 2px solid #cbd5e1;">
                            <?php else: ?>
                                <img id="photoPreview" src="#" alt="Preview" style="display:none; width: 75px; height: 75px; object-fit: cover; border-radius: 10px; border: 2px solid #cbd5e1;">
                            <?php endif; ?>
                            <small class="text-muted">Leave empty to keep existing photo.</small>
                        </div>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top d-flex gap-3">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Update Student Record
                        </button>
                        <a href="students.php" class="btn btn-light border px-4 py-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
