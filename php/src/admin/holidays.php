<?php
/**
 * Admin View - Holidays
 */

session_start();

require_once __DIR__ . '/../auth/Auth.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../utils/DateHelper.php';

// Require admin login
Auth::requireAdmin();

$adminUsername = Auth::getAdminUsername();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - วันหยุด</title>
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
            <a href="schedule.php">📅 ตารางเวรทั้งปี</a>
            <a href="holidays.php" class="active">🎉 วันหยุด</a>
            <a href="conditions.php">📜 เงื่อนไข</a>
            <a href="logout.php">🚪 ออกจากระบบ</a>
        </nav>

        <div class="content">
            <div
                style="background: #e7f3ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <span><strong>👤 User:</strong> <?= htmlspecialchars($adminUsername) ?></span>
                <a href="index.php" class="badge badge-info" style="text-decoration: none;">← กลับหน้าหลัก</a>
            </div>

            <?php require __DIR__ . '/../views/holidays.php'; ?>
        </div>
    </div>
</body>

</html>