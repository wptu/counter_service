<?php
/**
 * Day of Week Distribution View
 */

$allStaff = $data['staff']->getAllStaff();
$daysOrder = ['จันทร์' => 1, 'อังคาร' => 2, 'พุธ' => 3, 'พฤหัสบดี' => 4, 'ศุกร์' => 5];
?>

<h2>การกระจายวันในสัปดาห์</h2>
<p>แสดงจำนวนเวรของแต่ละคนในแต่ละวัน (จันทร์-ศุกร์)</p>

<table>
    <thead>
        <tr>
            <th>รหัสบุคลากร</th>
            <th>ชื่อ</th>
            <th>กลุ่ม</th>
            <th>จันทร์</th>
            <th>อังคาร</th>
            <th>พุธ</th>
            <th>พฤหัสบดี</th>
            <th>ศุกร์</th>
            <th>รวม</th>
            <th>ส่วนต่าง</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($allStaff as $staffCode):
            $displayName = $data['staff']->getDisplayName($staffCode);
            $group = $data['staff']->getGroup($staffCode);
            $dowCounts = $data['stats']['dow_counts'][$staffCode] ?? [];

            $counts = [
                $dowCounts[1] ?? 0, // Mon
                $dowCounts[2] ?? 0, // Tue
                $dowCounts[3] ?? 0, // Wed
                $dowCounts[4] ?? 0, // Thu
                $dowCounts[5] ?? 0  // Fri
            ];

            $total = $data['stats']['total_shifts'][$staffCode] ?? 0;
            $max = max($counts);
            $min = min($counts);
            $diff = $max - $min;

            $diffClass = '';
            if ($diff <= 2) {
                $diffClass = 'badge-success';
            } elseif ($diff > 4) {
                $diffClass = 'badge-danger';
            } else {
                $diffClass = 'badge-warning';
            }
            ?>
            <tr>
                <td><?= htmlspecialchars($staffCode) ?></td>
                <td><?= htmlspecialchars($displayName) ?></td>
                <td><span class="badge <?= $group === 'A' ? 'badge-info' : 'badge-success' ?>"><?= $group ?></span></td>
                <td><?= $counts[0] ?></td>
                <td><?= $counts[1] ?></td>
                <td><?= $counts[2] ?></td>
                <td><?= $counts[3] ?></td>
                <td><?= $counts[4] ?></td>
                <td><strong><?= $total ?></strong></td>
                <td><span class="badge <?= $diffClass ?>"><?= $diff ?></span></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
    <strong>หมายเหตุ:</strong>
    <ul style="margin: 10px 0 0 20px;">
        <li><span class="badge badge-success">เขียว</span> = ส่วนต่าง ≤ 2 (การกระจายดีมาก)</li>
        <li><span class="badge badge-warning">เหลือง</span> = ส่วนต่าง 3-4 (การกระจายปานกลาง)</li>
        <li><span class="badge badge-danger">แดง</span> = ส่วนต่าง > 4 (การกระจายไม่สมดุล)</li>
    </ul>
</div>