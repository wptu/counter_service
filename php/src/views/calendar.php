<?php
/**
 * Monthly Calendar View
 */

// Validate and cast month parameter
$monthIndex = (int) $month; // Convert to integer and use 0-based index

// Validate range
if ($monthIndex < 0 || $monthIndex > 11) {
    echo '<div style="background: #f8d7da; padding: 20px; border-radius: 8px; color: #721c24;">';
    echo '<h3>❌ เดือนไม่ถูกต้อง</h3>';
    echo '<p>กรุณาเลือกเดือนระหว่าง มกราคม - ธันวาคม (ได้รับ: ' . htmlspecialchars($month) . ')</p>';
    echo '<a href="?page=calendars" class="badge badge-info" style="text-decoration: none; padding: 10px 20px;">← กลับไปเลือกเดือน</a>';
    echo '</div>';
    return;
}

$monthName = THAI_MONTHS[$monthIndex];

// Filter calendar for this month
$monthDays = array_filter($data['calendar'], function ($day) use ($monthIndex) {
    $dateParts = explode('/', $day['date']);
    return isset($dateParts[1]) && ((int) $dateParts[1] - 1) === $monthIndex;
});

// Get first day of month
$firstDay = new DateTime(YEAR . '-' . sprintf('%02d', $monthIndex + 1) . '-01', new DateTimeZone(TIMEZONE));
$startDow = (int) $firstDay->format('w');

// Get last day of month (safe way)
$lastDay = clone $firstDay;
$lastDay->modify('last day of this month');
$numDays = (int) $lastDay->format('d');

// Build calendar grid
$calendarData = [];
foreach ($monthDays as $day) {
    $dateParts = explode('/', $day['date']);
    $dayNum = (int) $dateParts[0];
    $calendarData[$dayNum] = $day;
}
?>

<style>
    .month-title {
        text-align: center;
        color: #333333;
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
    }

    .nav-btn {
        text-decoration: none;
        color: #6c757d;
        font-size: 1.2rem;
        padding: 5px 10px;
        border-radius: 5px;
        transition: background 0.2s;
    }

    .nav-btn:hover {
        background: #e9ecef;
        color: #495057;
    }

    .nav-btn.disabled {
        color: #dee2e6;
        pointer-events: none;
    }

    /* ... existing CSS ... */
    .calendar-grid {
        display: grid;
        grid-template-columns: 0.6fr 1fr 1fr 1fr 1fr 1fr 0.6fr;
        gap: 10px;
        margin: 20px 0;
    }

    /* ... rest of CSS ... */
</style>

<div class="month-title">
    <?php if ($monthIndex > 0): ?>
        <a href="?page=calendars&month=<?= $monthIndex - 1 ?>" class="nav-btn">❮</a>
    <?php else: ?>
        <span class="nav-btn disabled">❮</span>
    <?php endif; ?>

    <div style="display: flex; align-items: center; gap: 15px;">
        <h2 style="margin: 0;"><?= $monthName ?> <?= YEAR + 543 ?></h2>
        <a href="export_pdf.php?year=<?= YEAR ?>&month=<?= $monthIndex + 1 ?>" target="_blank"
            style="background: #e74c3c; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 0.9rem; display: flex; align-items: center; gap: 5px;">
            <span>📄 Export PDF</span>
        </a>
    </div>

    <?php if ($monthIndex < 11): ?>
        <a href="?page=calendars&month=<?= $monthIndex + 1 ?>" class="nav-btn">❯</a>
    <?php else: ?>
        <span class="nav-btn disabled">❯</span>
    <?php endif; ?>
</div>


<div class="table-responsive">
    <div class="calendar-grid" style="min-width: 900px;">
        <?php foreach (THAI_DAYS as $dayName): ?>
            <div class="calendar-header"><?= $dayName ?></div>
        <?php endforeach; ?>

        <?php
        // Empty cells before first day
        for ($i = 0; $i < $startDow; $i++): ?>
            <div class="calendar-cell"></div>
        <?php endfor; ?>

        <?php for ($day = 1; $day <= $numDays; $day++):
            $dayData = $calendarData[$day] ?? null;
            $cellClass = 'calendar-cell';

            if ($dayData) {
                if ($dayData['is_weekend'])
                    $cellClass .= ' weekend';
                if ($dayData['is_holiday'])
                    $cellClass .= ' holiday';
            }
            ?>
            <div class="<?= $cellClass ?>">
                <div class="date-num"><?= $day ?></div>
                <?php if ($dayData && $dayData['is_working']): ?>
                    <div class="shift-info">
                        <strong>ทพ.:</strong> <span
                            style="white-space: nowrap;"><?= htmlspecialchars($dayData['tp_a']) ?></span>,
                        <span style="white-space: nowrap;"><?= htmlspecialchars($dayData['tp_b']) ?></span><br>

                        <?php if (!empty($dayData['rs_morning']) && $dayData['rs_morning'] !== '-'): ?>
                            <strong>รส.(เช้า):</strong> <span
                                style="white-space: nowrap;"><?= htmlspecialchars($dayData['rs_morning']) ?></span><br>
                            <strong>รส.(บ่าย):</strong> <span
                                style="white-space: nowrap;"><?= htmlspecialchars($dayData['rs_afternoon']) ?></span>
                        <?php else: ?>
                            <strong>รส.:</strong> <span style="white-space: nowrap;"><?= htmlspecialchars($dayData['rs']) ?></span>
                        <?php endif; ?>
                    </div>
                <?php elseif ($dayData && $dayData['is_holiday']): ?>
                    <div class="shift-info" style="color: #c1272d;">
                        <?= htmlspecialchars($dayData['holiday']) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>

<div style="margin-top: 20px;">
    <a href="?page=calendars" class="badge badge-info" style="text-decoration: none; padding: 10px 20px;">←
        กลับไปเลือกเดือน</a>
</div>