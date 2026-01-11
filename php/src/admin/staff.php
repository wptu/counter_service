<?php
/**
 * Staff Management Interface
 */

session_start();

require_once __DIR__ . '/../auth/Auth.php';
require_once __DIR__ . '/../models/StaffDB.php';

// Require admin login
Auth::requireAdmin();

$staffDB = new StaffDB();
$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $group = $_POST['group'] ?? '';

        if ($code && $name && in_array($group, ['A', 'B'])) {
            if ($staffDB->codeExists($code)) {
                $message = "รหัส $code มีอยู่ในระบบแล้ว";
                $messageType = 'error';
            } else {
                $staffDB->create($code, $name, $group);
                $message = "เพิ่มพนักงาน $name สำเร็จ";
                $messageType = 'success';
            }
        } else {
            $message = "กรุณากรอกข้อมูลให้ครบถ้วน";
            $messageType = 'error';
        }
    } elseif ($action === 'edit') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($id && $name) {
            if ($staffDB->update($id, $name)) {
                $message = "แก้ไขข้อมูลสำเร็จ";
                $messageType = 'success';
            } else {
                $message = "เกิดข้อผิดพลาดในการแก้ไข";
                $messageType = 'error';
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id) {
            if ($staffDB->delete($id)) {
                $message = "ลบพนักงานสำเร็จ";
                $messageType = 'success';
            } else {
                $message = "เกิดข้อผิดพลาดในการลบ";
                $messageType = 'error';
            }
        }
    }
}

// Get all staff
$allStaff = $staffDB->getAll();

$adminUsername = Auth::getAdminUsername();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการพนักงาน - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/css/style.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close {
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }

        .close:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            font-size: 1rem;
            font-family: 'Sarabun', sans-serif;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-primary {
            background: #4a86e8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
        }

        .action-btns {
            display: flex;
            gap: 5px;
        }

        .action-btns button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn-edit {
            background: #ffc107;
            color: #000;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>👥 จัดการพนักงาน</h1>
            <p>เพิ่ม แก้ไข และลบข้อมูลพนักงาน</p>
        </header>

        <nav>
            <a href="index.php">📊 Dashboard</a>
            <a href="staff.php" class="active">👥 จัดการพนักงาน</a>
            <a href="../">👁️ ดูตารางเวร</a>
            <a href="logout.php">🚪 ออกจากระบบ</a>
        </nav>

        <div class="content">
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <button onclick="showAddModal()" class="btn-primary" style="margin-bottom: 20px;">
                ➕ เพิ่มพนักงานใหม่
            </button>

            <h2>รายชื่อพนักงาน (<?= count($allStaff) ?> คน)</h2>

            <table>
                <thead>
                    <tr>
                        <th>รหัส</th>
                        <th>ชื่อ</th>
                        <th>กลุ่ม</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allStaff as $staff): ?>
                        <tr>
                            <td><?= htmlspecialchars($staff['code']) ?></td>
                            <td><?= htmlspecialchars($staff['name']) ?></td>
                            <td><span
                                    class="badge badge-<?= $staff['group_type'] === 'A' ? 'info' : 'success' ?>"><?= $staff['group_type'] ?></span>
                            </td>
                            <td><?= $staff['active'] ? '✅ ใช้งาน' : '❌ ระงับ' ?></td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn-edit"
                                        onclick="showEditModal(<?= $staff['id'] ?>, '<?= htmlspecialchars($staff['name'], ENT_QUOTES) ?>')">
                                        ✏️ แก้ไข
                                    </button>
                                    <button class="btn-delete"
                                        onclick="confirmDelete(<?= $staff['id'] ?>, '<?= htmlspecialchars($staff['code'], ENT_QUOTES) ?>')">
                                        🗑️ ลบ
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>เพิ่มพนักงานใหม่</h2>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>รหัสพนักงาน *</label>
                    <input type="text" name="code" required placeholder="เช่น A11, B16">
                </div>
                <div class="form-group">
                    <label>ชื่อ-นามสกุล *</label>
                    <input type="text" name="name" required placeholder="ชื่อพนักงาน">
                </div>
                <div class="form-group">
                    <label>กลุ่ม *</label>
                    <select name="group" required>
                        <option value="">-- เลือกกลุ่ม --</option>
                        <option value="A">กลุ่ม A</option>
                        <option value="B">กลุ่ม B</option>
                    </select>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn-primary">บันทึก</button>
                    <button type="button" class="btn-secondary" onclick="closeModal('addModal')">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>แก้ไขข้อมูลพนักงาน</h2>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-group">
                    <label>ชื่อ-นามสกุล *</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn-primary">บันทึก</button>
                    <button type="button" class="btn-secondary" onclick="closeModal('editModal')">ยกเลิก</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Form (hidden) -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="delete_id">
    </form>

    <script>
        function showAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function showEditModal(id, name) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function confirmDelete(id, code) {
            if (confirm('คุณแน่ใจหรือไม่ที่จะลบพนักงาน ' + code + '?\n\nการลบจะทำให้ต้องสร้างตารางเวรใหม่')) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Close modals when clicking outside
        window.onclick = function (event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>

</html>