<?php
/**
 * Summary View - Staff shift statistics
 */

$allStaff = $data['staff']->getAllStaff();
?>

<h2>สรุปจำนวนเวรแต่ละคน</h2>

<table>
    <thead>
        <tr>
            <th>รหัสบุคลากร</th>
            <th>ชื่อ</th>
            <th>กลุ่ม</th>
            <th>จำนวนเวร ทพ.</th>
            <th>จำนวนเวร รส.</th>
            <th>รวมทั้งหมด</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($allStaff as $staffCode):
            $displayName = $data['staff']->getDisplayName($staffCode);
            $group = $data['staff']->getGroup($staffCode);
            $tpCount = $data['stats']['tp_counts'][$staffCode] ?? 0;
            $rsCount = $data['stats']['rs_counts'][$staffCode] ?? 0;
            $total = $data['stats']['total_shifts'][$staffCode] ?? 0;
            ?>
            <tr>
                <td><?= htmlspecialchars($staffCode) ?></td>
                <td><?= htmlspecialchars($displayName) ?></td>
                <td><span class="badge <?= $group === 'A' ? 'badge-info' : 'badge-success' ?>"><?= $group ?></span></td>
                <td><?= $tpCount ?></td>
                <td><?= $rsCount ?></td>
                <td><strong><?= $total ?></strong></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>