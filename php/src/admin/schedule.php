<?php
/**
 * Admin View - Full Year Schedule
 */

session_start();

require_once __DIR__ . '/../auth/Auth.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../scheduling/Scheduler.php';
require_once __DIR__ . '/../models/ScheduleDB.php';

// Require admin login
Auth::requireAdmin();

$adminUsername = Auth::getAdminUsername();

// Initialize scheduler and database
$scheduler = new Scheduler();
$scheduleDB = new ScheduleDB();

// Try to load schedule from database
$calendar = $scheduler->loadScheduleFromDB();

// If no schedule in database, generate new one (or just load it if exists)
if ($calendar === null) {
    // Ideally admin should go to regenerate page, but let's try to generate object structure
    // But practically, if no schedule, this view will be empty.
    $calendar = [];
}

// Get metadata
$scheduleMeta = $scheduleDB->getMeta(YEAR);
$workingDaysCount = $scheduleMeta ? $scheduleMeta['working_days_count'] : $scheduler->getWorkingDaysCount();
$rsGroupCounts = $scheduleMeta ? ['A' => $scheduleMeta['rs_group_a_count'], 'B' => $scheduleMeta['rs_group_b_count']] : $scheduler->getRsGroupCounts();

// Prepare data for view
$data = [
    'calendar' => $calendar,
    'working_days_count' => $workingDaysCount,
    'rs_group_counts' => $rsGroupCounts,
    'year' => YEAR
];

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - ตารางเวรทั้งปี</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/style.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>👑 Admin Dashboard</h1>
            <p>ระบบจัดการตารางเวรพนักงาน <?= YEAR ?></p>
        </header>

        <nav>
            <a href="index.php">📊 Dashboard</a>
            <a href="staff.php">👥 จัดการพนักงาน</a>
            <a href="schedule.php" class="active">📅 ตารางเวรทั้งปี</a>
            <a href="holidays.php">🎉 วันหยุด</a>
            <a href="conditions.php">📜 เงื่อนไข</a>
            <a href="logout.php">🚪 ออกจากระบบ</a>
        </nav>

        <div class="content">
            <div
                style="background: #e7f3ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <span><strong>👤 User:</strong> <?= htmlspecialchars($adminUsername) ?></span>
                <a href="index.php" class="badge badge-info" style="text-decoration: none;">← กลับหน้าหลัก</a>
            </div>

            <?php require __DIR__ . '/../views/schedule.php'; ?>
        </div>
    </div>
</body>

</html>