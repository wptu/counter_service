<?php
/**
 * Regenerate Schedule
 */

session_start();

require_once __DIR__ . '/../auth/Auth.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../scheduling/Scheduler.php';

// Require admin login
Auth::requireAdmin();

$message = '';
$messageType = '';

// Handle regenerate request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    try {
        $scheduler = new Scheduler();

        // Generate new schedule
        $calendar = $scheduler->generateSchedule();

        $message = "สร้างตารางเวรใหม่สำเร็จ! จำนวนวันทำการ: " . $scheduler->getWorkingDaysCount() . " วัน";
        $messageType = 'success';

    } catch (Exception $e) {
        $message = "เกิดข้อผิดพลาด: " . $e->getMessage();
        $messageType = 'error';
    }
}

$adminUsername = Auth::getAdminUsername();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างตารางเวรใหม่ - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }

        .danger-box {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }

        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }

        .btn-large {
            padding: 15px 40px;
            font-size: 1.2rem;
            margin: 10px;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>🔄 สร้างตารางเวรใหม่</h1>
            <p>Regenerate Schedule สำหรับปี <?= YEAR ?></p>
        </header>

        <nav>
            <a href="index.php">📊 Dashboard</a>
            <a href="staff.php">👥 จัดการพนักงาน</a>
            <a href="../">👁️ ดูตารางเวร</a>
            <a href="logout.php">🚪 ออกจากระบบ</a>
        </nav>

        <div class="content">
            <?php if ($message): ?>
                <div class="<?= $messageType === 'success' ? 'success-box' : 'danger-box' ?>">
                    <h3><?= $messageType === 'success' ? '✅ สำเร็จ!' : '❌ เกิดข้อผิดพลาด' ?></h3>
                    <p><?= htmlspecialchars($message) ?></p>

                    <?php if ($messageType === 'success'): ?>
                        <a href="../"
                            style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 6px;">
                            👁️ ดูตารางเวรที่สร้างใหม่
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="danger-box">
                    <h2>⚠️ คำเตือน</h2>
                    <p><strong>การสร้างตารางเวรใหม่จะลบตารางเก่าทั้งหมด!</strong></p>
                    <ul style="margin: 15px 0 15px 30px;">
                        <li>ตารางเวรเก่าทั้งหมดจะถูกลบ</li>
                        <li>ระบบจะคำนวณตารางเวรใหม่จากรายชื่อพนักงานปัจจุบัน</li>
                        <li>กระบวนการอาจใช้เวลา 5-10 วินาที</li>
                    </ul>
                </div>

                <div class="warning-box">
                    <h3>📋 การสร้างตารางเวรจะใช้:</h3>
                    <ul style="margin: 15px 0 15px 30px;">
                        <li><strong>รายชื่อพนักงาน:</strong> จากฐานข้อมูลปัจจุบัน (ทั้งกลุ่ม A และ B)</li>
                        <li><strong>ปี:</strong> <?= YEAR ?> (<?= YEAR + 543 ?> พ.ศ.)</li>
                        <li><strong>วันหยุด:</strong> ตามปฏิทินวันหยุดราชการไทย</li>
                        <li><strong>เงื่อนไข:</strong> ตามที่กำหนดไว้ในระบบ</li>
                    </ul>
                </div>

                <form method="POST" style="text-align: center; margin-top: 30px;"
                    onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะสร้างตารางเวรใหม่?\n\nตารางเวรเก่าทั้งหมดจะถูกลบ');">
                    <input type="hidden" name="confirm" value="1">
                    <button type="submit" class="btn-danger btn-large">
                        🔄 สร้างตารางเวรใหม่
                    </button>
                    <br>
                    <a href="index.php" class="btn-cancel btn-large"
                        style="display: inline-block; text-decoration: none; margin-top: 10px;">
                        ← ยกเลิก กลับไป Dashboard
                    </a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>