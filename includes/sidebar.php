<?php
// includes/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
$admin_name = $_SESSION['admin_fullname'] ?? 'Administrator';
$admin_user = $_SESSION['admin_username'] ?? 'admin';
?>
<!-- Sidebar Navigation -->
<aside class="sidebar no-print">
    <div class="sidebar-header">
        <a href="dashboard.php" class="sidebar-brand">
            <i class="fa-solid fa-graduation-cap"></i>
            <div>
                <div style="font-size: 1.15rem; line-height: 1.2;">SRMS Portal</div>
                <small style="font-size: 0.75rem; color: #94a3b8; font-weight: normal;">Student Records System</small>
            </div>
        </a>
    </div>

    <ul class="sidebar-menu">
        <li class="sidebar-menu-title">Main Navigation</li>
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="sidebar-menu-title">Student Records</li>
        <li class="nav-item">
            <a href="students.php" class="nav-link <?php echo ($current_page == 'students.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i>
                <span>All Students</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="student_add.php" class="nav-link <?php echo ($current_page == 'student_add.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-user-plus"></i>
                <span>Add Student</span>
            </a>
        </li>

        <li class="sidebar-menu-title">Academic & Master</li>
        <li class="nav-item">
            <a href="courses.php" class="nav-link <?php echo ($current_page == 'courses.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-book-open"></i>
                <span>Courses & Branches</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="export.php" class="nav-link">
                <i class="fa-solid fa-file-excel"></i>
                <span>Export to CSV</span>
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="avatar" style="background:#6366f1; color:white; width:36px; height:36px;">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div style="line-height: 1.2;">
                    <div style="color:white; font-size: 0.85rem; font-weight: 600;"><?php echo htmlspecialchars($admin_name); ?></div>
                    <small style="color:#94a3b8; font-size: 0.72rem;">@<?php echo htmlspecialchars($admin_user); ?></small>
                </div>
            </div>
            <a href="logout.php" class="btn btn-sm btn-outline-danger" title="Logout" style="border-radius: 8px;">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </div>
</aside>
