<?php
/**
 * Main Schedule View - Shows full year schedule table
 */
?>

<div class="stats-grid">
    <div class="stat-card">
        <h3>วันทำการทั้งปี</h3>
        <div class="value"><?= $data['working_days_count'] ?></div>
    </div>
    <div class="stat-card">
        <h3>RS กลุ่ม B</h3>
        <div class="value"><?= $data['rs_group_counts']['B'] ?></div>
    </div>
    <div class="stat-card">
        <h3>RS กลุ่ม A</h3>
        <div class="value"><?= $data['rs_group_counts']['A'] ?></div>
    </div>
    <div class="stat-card">
        <h3>สัดส่วน B:A</h3>
        <div class="value">
            <?php
            $total = $data['rs_group_counts']['B'] + $data['rs_group_counts']['A'];
            if ($total > 0) {
                $ratio = round(($data['rs_group_counts']['B'] / $total) * 100);
                echo $ratio . ':' . (100 - $ratio);
            } else {
                echo '-';
            }
            ?>
        </div>
    </div>
</div>

<h2>ตารางเวรประจำปี <?= YEAR ?></h2>

<?php
// Group days by month
$calendarByMonth = [];
foreach ($data['calendar'] as $day) {
    $dateParts = explode('/', $day['date']);
    $month = isset($dateParts[1]) ? (int) $dateParts[1] : 0;
    $calendarByMonth[$month][] = $day;
}

// Thai Month Names
$thaiMonths = [
    1 => 'มกราคม',
    2 => 'กุมภาพันธ์',
    3 => 'มีนาคม',
    4 => 'เมษายน',
    5 => 'พฤษภาคม',
    6 => 'มิถุนายน',
    7 => 'กรกฎาคม',
    8 => 'สิงหาคม',
    9 => 'กันยายน',
    10 => 'ตุลาคม',
    11 => 'พฤศจิกายน',
    12 => 'ธันวาคม'
];
?>

<?php foreach ($calendarByMonth as $month => $days): ?>
    <div class="month-container" style="margin-bottom: 25px;">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; background: #f8f9fa; padding: 10px; border-radius: 5px; border-left: 5px solid #4a86e8;">
            <h3 style="margin: 0; color: #2c3e50;">
                <?= $thaiMonths[$month] ?? 'เดือนที่ ' . $month ?>
            </h3>
            <a href="export_pdf.php?year=<?= $data['year'] ?>&month=<?= $month ?>" target="_blank"
                class="btn btn-sm btn-outline-danger"
                style="text-decoration: none; background: #e74c3c; color: white; padding: 5px 15px; border-radius: 4px; font-size: 0.9em; display: flex; align-items: center; gap: 5px;">
                <span>📄 Export PDF</span>
            </a>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">วันที่</th>
                        <th style="width: 10%;">วัน</th>
                        <th style="width: 15%;">ทพ. - A</th>
                        <th style="width: 15%;">ทพ. - B</th>
                        <th style="width: 15%;">รส. (เช้า/บ่าย)</th>
                        <th style="width: 10%;">วันหยุด</th>
                        <th>หมายเหตุ/เหตุผล</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($days as $day):
                        // Determine row class
                        $rowClass = '';
                        if ($day['is_weekend']) {
                            $rowClass = 'weekend';
                        } elseif ($day['is_holiday']) {
                            $rowClass = 'holiday';
                        } elseif ($day['is_working']) {
                            $rowClass = 'working';
                        }
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td><?= htmlspecialchars($day['date']) ?></td>
                            <td><?= htmlspecialchars($day['day']) ?></td>
                            <td style="white-space: nowrap;"><?= htmlspecialchars($day['tp_a']) ?></td>
                            <td style="white-space: nowrap;"><?= htmlspecialchars($day['tp_b']) ?></td>
                            <td style="white-space: nowrap;">
                                <?php if (!empty($day['rs_morning']) && $day['rs_morning'] !== '-'): ?>
                                    <div style="font-size: 0.9em;"><strong style="color: #e67e22;">เช้า:</strong>
                                        <?= htmlspecialchars($day['rs_morning']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($day['rs_afternoon']) && $day['rs_afternoon'] !== '-'): ?>
                                    <div style="font-size: 0.9em;"><strong style="color: #d35400;">บ่าย:</strong>
                                        <?= htmlspecialchars($day['rs_afternoon']) ?></div>
                                <?php endif; ?>
                                <?php if (
                                    (empty($day['rs_morning']) || $day['rs_morning'] === '-') &&
                                    (empty($day['rs_afternoon']) || $day['rs_afternoon'] === '-')
                                ): ?>
                                    <?= htmlspecialchars($day['rs']) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($day['holiday']) ?></td>
                            <td style="font-size: 0.85rem;"><?= htmlspecialchars($day['remark']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>