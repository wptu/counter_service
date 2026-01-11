<?php
/**
 * Admin Dashboard
 */

session_start();

require_once __DIR__ . '/../auth/Auth.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/StaffDB.php';
require_once __DIR__ . '/../models/ScheduleDB.php';

// Require admin login
Auth::requireAdmin();

$staffDB = new StaffDB();
$scheduleDB = new ScheduleDB();

// Get statistics
$groupACount = $staffDB->getCountByGroup('A');
$groupBCount = $staffDB->getCountByGroup('B');
$totalStaff = $groupACount + $groupBCount;

$scheduleMeta = $scheduleDB->getMeta(YEAR);
$scheduleExists = $scheduleDB->scheduleExists(YEAR);

$adminUsername = Auth::getAdminUsername();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ระบบจัดตารางเวร</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/style.css">
</head>

<body>
    <div class="container">
        <header>
            <h1>ระบบจัดตารางเวร <?= YEAR + 543 ?> (Admin)</h1>
            <div class="subtitle">จัดการข้อมูลและสร้างตารางเวร</div>
        </header>

        <nav>
            <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">📊
                Dashboard</a>
            <a href="staff.php">👥 จัดการพนักงาน</a>
            <a href="schedule.php">📅 ตารางเวรทั้งปี</a>
            <a href="holidays.php">🎉 วันหยุด</a>
            <a href="conditions.php">📜 เงื่อนไข</a>
            <a href="settings.php">⚙️ ตั้งค่าระบบ</a>
            <a href="logout.php" style="background: #ef5350; color: white; float: right;">🚪 ออกจากระบบ</a>
        </nav>

        <div class="content">
            <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <strong>👤 User:</strong> <?= htmlspecialchars($adminUsername) ?> (Administrator)
            </div>

            <h2>สถิติระบบ</h2>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>พนักงานทั้งหมด</h3>
                    <div class="value"><?= $totalStaff ?></div>
                </div>
                <!-- ... existing stats ... -->
                <div class="stat-card">
                    <h3>กลุ่ม A</h3>
                    <div class="value"><?= $groupACount ?></div>
                </div>
                <div class="stat-card">
                    <h3>กลุ่ม B</h3>
                    <div class="value"><?= $groupBCount ?></div>
                </div>
                <div class="stat-card">
                    <h3>สถานะตาราง</h3>
                    <div class="value"><?= $scheduleExists ? '✅' : '⚠️' ?></div>
                </div>
            </div>

            <!-- ... existing metadata table ... -->
            <?php if ($scheduleMeta): ?>
                <h2>ข้อมูลตารางเวรปัจจุบัน</h2>
                <!-- ... table code ... -->
                <table>
                    <!-- ... existing rows ... -->
                    <tr>
                        <th>รายการ</th>
                        <th>ค่า</th>
                    </tr>
                    <tr>
                        <td>ปี</td>
                        <td><?= $scheduleMeta['year'] ?></td>
                    </tr>
                    <tr>
                        <td>วันทำการทั้งหมด</td>
                        <td><?= $scheduleMeta['working_days_count'] ?> วัน</td>
                    </tr>
                    <tr>
                        <td>RS กลุ่ม A</td>
                        <td><?= $scheduleMeta['rs_group_a_count'] ?> ครั้ง</td>
                    </tr>
                    <tr>
                        <td>RS กลุ่ม B</td>
                        <td><?= $scheduleMeta['rs_group_b_count'] ?> ครั้ง</td>
                    </tr>
                    <tr>
                        <td>สัดส่วน B:A</td>
                        <td>
                            <?php
                            $total = $scheduleMeta['rs_group_a_count'] + $scheduleMeta['rs_group_b_count'];
                            if ($total > 0) {
                                $bRatio = round(($scheduleMeta['rs_group_b_count'] / $total) * 100);
                                echo $bRatio . ':' . (100 - $bRatio);
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>สร้างเมื่อ</td>
                        <td><?= date('d/m/Y H:i:s', strtotime($scheduleMeta['generated_at'])) ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <div style="background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <h3>⚠️ ยังไม่มีตารางเวร</h3>
                    <p>กรุณาสร้างตารางเวรใหม่</p>
                </div>
            <?php endif; ?>

            <h2>การจัดการ</h2>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
                <a href="staff.php"
                    style="background: #4a86e8; color: white; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600;">
                    👥 จัดการพนักงาน
                </a>

                <a href="regenerate.php"
                    style="background: #f39c12; color: white; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600;">
                    🔄 สร้างตารางใหม่
                </a>

                <a href="schedule.php"
                    style="background: #27ae60; color: white; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600;">
                    📅 ดูตารางเวรทั้งปี
                </a>

                <a href="holidays.php"
                    style="background: #e91e63; color: white; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600;">
                    🎉 วันหยุด
                </a>

                <a href="conditions.php"
                    style="background: #607d8b; color: white; padding: 20px; border-radius: 8px; text-decoration: none; text-align: center; font-weight: 600;">
                    📜 เงื่อนไขการจัดเวร
                </a>
            </div>
        </div>
    </div>
</body>

</html>