<?php
/**
 * Holidays View
 */

$holidayConfig = require __DIR__ . '/../config/holidays.php';
$holidays = $holidayConfig['holidays'];
$specialDays = $holidayConfig['special_working_days'];

// Prepare holidays array
$holidaysList = [];
foreach ($holidays as $dateKey => $name) {
    $date = DateTime::createFromFormat('Y-m-d', $dateKey, new DateTimeZone(TIMEZONE));
    $holidaysList[] = [
        'date' => $date,
        'dateKey' => $dateKey,
        'name' => $name,
        'type' => 'วันหยุดราชการ'
    ];
}

// Prepare special working days
foreach ($specialDays as $dateKey => $name) {
    $date = DateTime::createFromFormat('Y-m-d', $dateKey, new DateTimeZone(TIMEZONE));
    $holidaysList[] = [
        'date' => $date,
        'dateKey' => $dateKey,
        'name' => $name,
        'type' => 'วันทำงานพิเศษ'
    ];
}

// Sort by date
usort($holidaysList, function ($a, $b) {
    return $a['date'] <=> $b['date'];
});
?>

<h2>วันหยุดและวันทำงานพิเศษ ปี <?= YEAR ?></h2>

<div class="stats-grid" style="margin-bottom: 30px;">
    <div class="stat-card">
        <h3>วันหยุดราชการ</h3>
        <div class="value"><?= count($holidays) ?></div>
    </div>
    <div class="stat-card">
        <h3>วันทำงานพิเศษ</h3>
        <div class="value"><?= count($specialDays) ?></div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>วันที่</th>
            <th>วัน</th>
            <th>ชื่อวันหยุด/วันทำงานพิเศษ</th>
            <th>ประเภท</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($holidaysList as $item):
            $dateDisplay = DateHelper::formatDateDisplay($item['date']);
            $dayName = DateHelper::getThaiDayName($item['date']);
            $rowClass = $item['type'] === 'วันหยุดราชการ' ? 'holiday' : '';
            $badgeClass = $item['type'] === 'วันหยุดราชการ' ? 'badge-danger' : 'badge-warning';
            ?>
            <tr class="<?= $rowClass ?>">
                <td><?= $dateDisplay ?></td>
                <td><?= $dayName ?></td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= $item['type'] ?></span></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>