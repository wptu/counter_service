<?php
/**
 * Monthly TP Summary View
 */

$allStaff = $data['staff']->getAllStaff();
?>

<h2>สรุปเวร TP รายเดือน</h2>
<p>แสดงจำนวนเวร TP ของแต่ละคนในแต่ละเดือน</p>

<table>
    <thead>
        <tr>
            <th>รหัสบุคลากร</th>
            <th>ชื่อ</th>
            <th>กลุ่ม</th>
            <?php foreach (THAI_MONTHS_SHORT as $monthShort): ?>
                <th><?= $monthShort ?></th>
            <?php endforeach; ?>
            <th>รวม</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($allStaff as $staffCode):
            $displayName = $data['staff']->getDisplayName($staffCode);
            $group = $data['staff']->getGroup($staffCode);
            $monthlyTp = $data['stats']['monthly_tp_counts'][$staffCode] ?? [];

            $yearTotal = 0;
            ?>
            <tr>
                <td><?= htmlspecialchars($staffCode) ?></td>
                <td><?= htmlspecialchars($displayName) ?></td>
                <td><span class="badge <?= $group === 'A' ? 'badge-info' : 'badge-success' ?>"><?= $group ?></span></td>
                <?php for ($m = 1; $m <= 12; $m++):
                    $count = $monthlyTp[$m] ?? 0;
                    $yearTotal += $count;

                    // Color coding for Group A (should be 2-3 per month)
                    $cellClass = '';
                    if ($group === 'A') {
                        if ($count >= 2 && $count <= 3) {
                            $cellClass = 'style="background: #b7e1cd;"';
                        } elseif ($count > 3) {
                            $cellClass = 'style="background: #f4c7c3;"';
                        } elseif ($count < 2) {
                            $cellClass = 'style="background: #fff2cc;"';
                        }
                    }
                    ?>
                    <td <?= $cellClass ?>><?= $count ?></td>
                <?php endfor; ?>
                <td><strong><?= $yearTotal ?></strong></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
    <strong>หมายเหตุสำหรับกลุ่ม A:</strong>
    <ul style="margin: 10px 0 0 20px;">
        <li style="background: #b7e1cd; padding: 5px; margin: 3px 0;">2-3 ครั้ง/เดือน = ดีมาก (ตามเป้าหมาย)</li>
        <li style="background: #fff2cc; padding: 5px; margin: 3px 0;">
            < 2 ครั้ง/เดือน=น้อยไป</li>
        <li style="background: #f4c7c3; padding: 5px; margin: 3px 0;">> 3 ครั้ง/เดือน = มากไป</li>
    </ul>
    <strong>หมายเหตุสำหรับกลุ่ม B:</strong>
    <ul style="margin: 10px 0 0 20px;">
        <li>ต้องมีอย่างน้อย 1 ครั้ง/เดือน (ไม่แสดงสีเพื่อความชัดเจน)</li>
    </ul>
</div>