<?php
/**
 * System Settings Page
 */

session_start();

require_once __DIR__ . '/../auth/Auth.php';
require_once __DIR__ . '/../models/Settings.php';

// Require admin login
Auth::requireAdmin();

$success = null;
$error = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $publicLoginEnabled = isset($_POST['public_login_enabled']) ? '1' : '0';

    if (Settings::set('public_login_enabled', $publicLoginEnabled)) {
        $success = "บันทึกการตั้งค่าเรียบร้อยแล้ว";
    } else {
        $error = "เกิดข้อผิดพลาดในการบันทึกค่า";
    }
}

// Get current settings
$publicLoginEnabled = Settings::get('public_login_enabled', '1');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งค่าระบบ - ระบบจัดตารางเวร</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .setting-item {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .setting-info h3 {
            margin: 0 0 5px 0;
            color: #37474f;
        }

        .setting-info p {
            margin: 0;
            color: #78909c;
            font-size: 0.9rem;
        }

        /* Switch Toggle */
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>⚙️ ตั้งค่าระบบ</h1>
            <div class="subtitle">จัดการการตั้งค่าต่างๆ ของระบบ</div>
        </header>

        <nav>
            <a href="index.php">🔙 กลับหน้าหลัก</a>
            <a href="logout.php" style="background: #ef5350; color: white; float: right;">🚪 ออกจากระบบ</a>
        </nav>

        <div class="content">
            <?php if ($success): ?>
                <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="setting-item">
                    <div class="setting-info">
                        <h3>ล็อกรหัสผ่านสำหรับบุคคลทั่วไป (Public Login)</h3>
                        <p>หากปิด จะทำให้บุคคลทั่วไปสามารถเข้าดูตารางเวรได้ทันทีโดยไม่ต้องใส่รหัสผ่าน</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="public_login_enabled" value="1" <?= $publicLoginEnabled === '1' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="submit" class="btn"
                        style="padding: 12px 30px; font-size: 1rem;">บันทึกการตั้งค่า</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>