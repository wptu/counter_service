<?php
/**
 * Debug Page - Check System Status
 */

// Set timezone
date_default_timezone_set('Asia/Bangkok');

// Check if files exist before requiring
$files = [
    '/config/constants.php',
    '/config/database.php',
    '/scheduling/Scheduler.php',
    '/models/ScheduleDB.php',
    '/models/StaffDB.php'
];

foreach ($files as $file) {
    if (!file_exists(__DIR__ . $file)) {
        die("❌ Missing file: " . __DIR__ . $file);
    }
}

// Include required files
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/scheduling/Scheduler.php';
require_once __DIR__ . '/models/ScheduleDB.php';
require_once __DIR__ . '/models/StaffDB.php';

echo '<h1>System Debug Info</h1>';
echo '<pre>';

// Check database connection
try {
    $db = Database::getInstance()->getConnection();
    echo "✅ Database connection: OK\n\n";
} catch (Exception $e) {
    echo "❌ Database connection: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    exit;
}

// Check staff
try {
    $staffDB = new StaffDB();
    $allStaff = $staffDB->getAll();
    echo "Staff Count: " . count($allStaff) . "\n";
    echo "Group A: " . $staffDB->getCountByGroup('A') . "\n";
    echo "Group B: " . $staffDB->getCountByGroup('B') . "\n\n";
} catch (Exception $e) {
    echo "❌ Staff Check Failed: " . $e->getMessage() . "\n\n";
}

// Check schedules
try {
    $scheduleDB = new ScheduleDB();
    $scheduleExists = $scheduleDB->scheduleExists(YEAR);
    echo "Schedule exists for " . YEAR . ": " . ($scheduleExists ? 'YES' : 'NO') . "\n";

    if ($scheduleExists) {
        $meta = $scheduleDB->getMeta(YEAR);
        if ($meta) {
            echo "Working days: " . $meta['working_days_count'] . "\n";
            echo "RS Group A: " . $meta['rs_group_a_count'] . "\n";
            echo "RS Group B: " . $meta['rs_group_b_count'] . "\n";
            echo "Generated at: " . $meta['generated_at'] . "\n";
        }

        // Count actual schedule rows
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT COUNT(*) as cnt FROM schedules WHERE year = " . YEAR);
        $count = $stmt->fetch();
        echo "Schedule rows in DB: " . $count['cnt'] . "\n\n";
    } else {
        echo "\n⚠️ NO SCHEDULE IN DATABASE!\n";
        echo "You need to:\n";
        echo "1. Login to admin: http://localhost:8080/admin/login.php\n";
        echo "2. Go to 'สร้างตารางใหม่' (Create New Schedule)\n";
        echo "3. Click regenerate button\n\n";
    }
} catch (Exception $e) {
    echo "❌ Schedule Check Failed: " . $e->getMessage() . "\n\n";
}

// Try to load scheduler
try {
    $scheduler = new Scheduler();
    $calendar = $scheduler->loadScheduleFromDB();

    if ($calendar === null) {
        echo "❌ No calendar loaded from DB (loadScheduleFromDB returned null)\n";
    } else {
        echo "✅ Calendar loaded: " . count($calendar) . " days\n";

        // Show first 3 days
        echo "\nFirst 3 working days:\n";
        $count = 0;
        foreach ($calendar as $day) {
            if ($day['is_working']) {
                echo json_encode($day, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                $count++;
                if ($count >= 3)
                    break;
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Scheduler error: " . $e->getMessage() . "\n";
}

echo '</pre>';
?>