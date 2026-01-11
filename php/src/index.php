<?php
/**
 * Main Entry Point for Shift Scheduling System (Database Version)
 */

// Set timezone
date_default_timezone_set('Asia/Bangkok');

// Include required files
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/auth/Auth.php'; // Include Auth first
require_once __DIR__ . '/scheduling/Scheduler.php';
require_once __DIR__ . '/models/ScheduleDB.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle Logout
if (isset($_GET['page']) && $_GET['page'] === 'logout') {
    Auth::logout();
    header('Location: ./');
    exit;
}

// Check Public Authentication
if (!Auth::isPublicAuthenticated()) {
    $error = null;
    // Check if submitting password
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['public_password'])) {
        if (Auth::loginPublic($_POST['public_password'])) {
            // Success, redirect to clear POST data
            header('Location: ./');
            exit;
        } else {
            $error = "รหัสผ่านไม่ถูกต้อง กรุณาลองใหม่";
        }
    }
    // Show public login form
    require __DIR__ . '/views/public_login.php';
    exit;
}

// Get requested page
$page = $_GET['page'] ?? 'calendars';
$month = isset($_GET['month']) ? (int) $_GET['month'] : null;

// Initialize scheduler and database
$scheduler = new Scheduler();
$scheduleDB = new ScheduleDB();

// Try to load schedule from database
$calendar = $scheduler->loadScheduleFromDB();

// If no schedule in database, generate new one
if ($calendar === null) {
    // For public view, if no schedule, we might just show empty or error, but let's stick to existing logic for now
    // Actually, generating logic here is fine for fallback, but ideally admin should do it.
    // If we rely on admin to regenerate, then $calendar is [] or null if file missing.
    // But Scheduler->generateSchedule() generates in memory.
}
// NOTE: We don't need to generate schedule here if user is public. We should just show what's in DB.
// But to avoid breaking existing logic, I'll leave the loading part.

// Get metadata
$scheduleMeta = $scheduleDB->getMeta(YEAR);
$workingDaysCount = $scheduleMeta ? $scheduleMeta['working_days_count'] : $scheduler->getWorkingDaysCount();
$rsGroupCounts = $scheduleMeta ? ['A' => $scheduleMeta['rs_group_a_count'], 'B' => $scheduleMeta['rs_group_b_count']] : $scheduler->getRsGroupCounts();

// Prepare data for views
$data = [
    'calendar' => $calendar,
    'staff' => $scheduler->getStaff(),
    'working_days_count' => $workingDaysCount,
    'rs_group_counts' => $rsGroupCounts,
    'year' => YEAR
];

$data['stats'] = []; // Empty or logic removed as not needed for public views
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดตารางเวร</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bai+Jamjuree:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>ระบบจัดตารางเวร <?= YEAR + 543 ?></h1>

        </header>

        <nav>
            <!-- Moved Full Schedule to Admin -->
            <a href="?page=calendars" class="<?= $page === 'calendars' ? 'active' : '' ?>">📅 ปฏิทินรายเดือน</a>
            <a href="?page=staff-details" class="<?= $page === 'staff-details' ? 'active' : '' ?>">👤
                รายละเอียดพนักงาน</a>
            <!-- Moved Holidays to Admin -->
            <!-- Moved Conditions to Admin -->
            <!-- Admin Login hidden (access via /admin) -->
            <?php if (Auth::isAdmin() || Settings::get('public_login_enabled', '1') === '1'): ?>
                <a href="?page=logout" style="background: #ef5350; color: white; float: right;">🚪 ออกจากระบบ</a>
            <?php endif; ?>
        </nav>

        <div class="content">
            <?php
            // Route to appropriate view
            switch ($page) {
                case 'calendars':
                    if ($month !== null && $month >= 0 && $month <= 11) {
                        require __DIR__ . '/views/calendar.php';
                    } else {
                        // Show month selection
                        require __DIR__ . '/views/month_selection.php';
                    }
                    break;

                case 'staff-details':
                    require __DIR__ . '/views/staff_details.php';
                    break;

                default:
                    // Redirect to calendars if page not found or restricted
                    if ($page !== 'calendars') {
                        // could do redirect but just requiring default is easier
                    }
                    // Default to month selection
                    require __DIR__ . '/views/month_selection.php';
                    break;
            }
            ?>
        </div>
    </div>

    <script src="/public/js/app.js"></script>
</body>

</html>