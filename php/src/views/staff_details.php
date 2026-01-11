<?php
/**
 * Staff Details View - Search and filter by staff member
 */

$allStaff = $data['staff']->getAllStaff();
$selectedStaff = $_GET['staff'] ?? '';
$selectedType = $_GET['type'] ?? 'ทั้งหมด';

// Build staff shift details
$staffShifts = [];
foreach ($data['calendar'] as $day) {
    if (!$day['is_working'])
        continue;

    $date = $day['date'];
    $dayName = $day['day'];

    // TP A
    if (isset($day['tp_a_code'])) {
        $staffShifts[$day['tp_a_code']][] = [
            'date' => $date,
            'day' => $dayName,
            'type' => 'ทพ.',
            'display_name' => $day['tp_a']
        ];
    }

    // TP B
    if (isset($day['tp_b_code'])) {
        $staffShifts[$day['tp_b_code']][] = [
            'date' => $date,
            'day' => $dayName,
            'type' => 'ทพ.',
            'display_name' => $day['tp_b']
        ];
    }

    // RS (Legacy/Fallback)
    if (isset($day['rs_code']) && !isset($day['rs_morning_code']) && !isset($day['rs_afternoon_code'])) {
        $staffShifts[$day['rs_code']][] = [
            'date' => $date,
            'day' => $dayName,
            'type' => 'รส.',
            'display_name' => $day['rs']
        ];
    }

    // RS Morning
    if (isset($day['rs_morning_code']) && $day['rs_morning_code'] !== '-') {
        $staffShifts[$day['rs_morning_code']][] = [
            'date' => $date,
            'day' => $dayName,
            'type' => 'รส.(เช้า)',
            'display_name' => $day['rs_morning']
        ];
    }

    // RS Afternoon
    if (isset($day['rs_afternoon_code']) && $day['rs_afternoon_code'] !== '-') {
        $staffShifts[$day['rs_afternoon_code']][] = [
            'date' => $date,
            'day' => $dayName,
            'type' => 'รส.(บ่าย)',
            'display_name' => $day['rs_afternoon']
        ];
    }
}
?>

<h2>รายละเอียดเวรของพนักงาน</h2>

<div class="filter-section">
    <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
        <input type="hidden" name="page" value="staff-details">

        <div>
            <label for="staff">เลือกพนักงาน:</label>
            <select name="staff" id="staff" onchange="this.form.submit()">
                <option value="">-- เลือกพนักงาน --</option>
                <?php foreach ($allStaff as $staffCode):
                    $displayName = $data['staff']->getDisplayName($staffCode);
                    $selected = ($staffCode === $selectedStaff) ? 'selected' : '';
                    ?>
                    <option value="<?= htmlspecialchars($staffCode) ?>" <?= $selected ?>>
                        <?= htmlspecialchars($displayName) ?> (<?= htmlspecialchars($staffCode) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="type">ประเภทเวร:</label>
            <select name="type" id="type" onchange="this.form.submit()">
                <option value="ทั้งหมด" <?= $selectedType === 'ทั้งหมด' ? 'selected' : '' ?>>ทั้งหมด</option>
                <option value="ทพ." <?= $selectedType === 'ทพ.' ? 'selected' : '' ?>>ทพ.</option>
                <option value="รส." <?= $selectedType === 'รส.' ? 'selected' : '' ?>>รส. (รวม)</option>
                <option value="รส.(เช้า)" <?= $selectedType === 'รส.(เช้า)' ? 'selected' : '' ?>>รส.(เช้า)</option>
                <option value="รส.(บ่าย)" <?= $selectedType === 'รส.(บ่าย)' ? 'selected' : '' ?>>รส.(บ่าย)</option>
            </select>
        </div>
    </form>
</div>

<?php if ($selectedStaff && isset($staffShifts[$selectedStaff])):
    $shifts = $staffShifts[$selectedStaff];

    // Filter by type
    if ($selectedType !== 'ทั้งหมด') {
        $shifts = array_filter($shifts, function ($shift) use ($selectedType) {
            // Include both morning and afternoon if "รส." is selected
            if ($selectedType === 'รส.') {
                return strpos($shift['type'], 'รส.') !== false;
            }
            return $shift['type'] === $selectedType;
        });
    }

    $displayName = $data['staff']->getDisplayName($selectedStaff);
    $group = $data['staff']->getGroup($selectedStaff);
    ?>

    <h3><?= htmlspecialchars($displayName) ?> (<?= htmlspecialchars($selectedStaff) ?>)
        <span class="badge <?= $group === 'A' ? 'badge-info' : 'badge-success' ?>"><?= $group ?></span>
    </h3>

    <table id="staff-details-table">
        <thead>
            <tr>
                <th>ลำดับ</th>
                <th>วันที่</th>
                <th>วัน</th>
                <th>ประเภทเวร</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $seq = 1;
            foreach ($shifts as $shift):
                // Parse date for special formatting
                $dateObj = DateTime::createFromFormat('d/m/Y', $shift['date']);
                $displayDate = $dateObj ? DateHelper::formatDateThaiFull($dateObj) : $shift['date'];

                // Determine badge class
                $badgeClass = 'badge-secondary';
                if ($shift['type'] === 'ทพ.') {
                    $badgeClass = 'badge-info';
                } elseif (strpos($shift['type'], 'รส.') !== false) {
                    $badgeClass = 'badge-warning';
                }
                ?>
                <tr>
                    <td style="text-align: center;"><?= $seq++ ?></td>
                    <td><?= htmlspecialchars($displayDate) ?></td>
                    <td><?= htmlspecialchars($shift['day']) ?></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($shift['type']) ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 15px;">
        <strong>สรุป:</strong> จำนวนเวรทั้งหมด <?= count($shifts) ?> ครั้ง
    </div>

<?php elseif ($selectedStaff): ?>
    <p style="padding: 20px; background: #fff2cc; border-radius: 8px;">ไม่พบข้อมูลเวรของพนักงานคนนี้</p>
<?php else: ?>
    <p style="padding: 20px; background: #f8f9fa; border-radius: 8px;">กรุณาเลือกพนักงานจากรายการด้านบน</p>
<?php endif; ?>