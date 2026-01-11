<?php
/**
 * Public Login View
 */
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบจัดตารางเวร</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #37474f;
        }

        .login-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .login-card h1 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #546e7a;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #78909c;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #cfd8dc;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: #546e7a;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: #546e7a;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-login:hover {
            background: #37474f;
        }

        .error-msg {
            background: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <h1>🔐 เข้าสู่ระบบ</h1>

        <?php if (isset($error)): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="./">
            <div class="form-group">
                <label for="public_password">รหัสผ่าน</label>
                <input type="password" id="public_password" name="public_password" class="form-control" required
                    autofocus placeholder="">
            </div>
            <button type="submit" class="btn-login">เข้าใช้งาน</button>
        </form>
    </div>
</body>

</html>