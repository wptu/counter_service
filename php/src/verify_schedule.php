<?php
// CLI Verification Script

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/holidays.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/utils/DateHelper.php';
require_once __DIR__ . '/models/Staff.php';
require_once __DIR__ . '/models/Schedule.php';
require_once __DIR__ . '/models/ScheduleDB.php';
require_once __DIR__ . '/scheduling/TpScheduler.php';
require_once __DIR__ . '/scheduling/RsScheduler.php';
require_once __DIR__ . '/scheduling/Scheduler.php';

try {
    echo "Starting schedule generation...\n";
    $scheduler = new Scheduler();
    $calendar = $scheduler->generateSchedule();
    echo "Schedule generated successfully.\n";
    echo "Total Working Days: " . $scheduler->getWorkingDaysCount() . "\n";

    // Verification 1: Check B12 in January and Feb 1-7
    echo "\n[Verification 1] Checking B12 presence in Jan and Feb 1-7...\n";
    $b12Errors = 0;
    foreach ($calendar as $day) {
        $date = DateTime::createFromFormat('d/m/Y', $day['date']);
        $month = (int) $date->format('n');
        $dayNum = (int) $date->format('j');

        if ($month == 1 || ($month == 2 && $dayNum <= 7)) { // Jan or Feb 1-7
            $rsm = $day['rs_morning_code'] ?? $day['rs_code']; // fallback
            $rsa = $day['rs_afternoon_code'] ?? $day['rs_code'];

            if (
                $day['tp_a_code'] === 'B12' || $day['tp_b_code'] === 'B12' ||
                $rsm === 'B12' || $rsa === 'B12'
            ) {
                $b12Errors++;
                echo "FAIL: Found B12 on " . $day['date'] . " (" . $day['day'] . ")\n";
            }
        }
    }
    if ($b12Errors === 0) {
        echo "PASS: B12 has 0 shifts in restricted period (Jan + Feb 1-7).\n";
    } else {
        echo "FAIL: B12 has $b12Errors shifts in restricted period.\n";
    }

    // Verification 2: Check standard month (March) - Wed/Fri should be TP closed
    echo "\n[Verification 2] Checking March (Standard Month) Wed/Fri...\n";
    $marchErrors = 0;
    foreach ($calendar as $day) {
        $date = DateTime::createFromFormat('d/m/Y', $day['date']);
        if ($date->format('n') == 3 && $day['is_working']) { // March
            $dow = (int) $date->format('w');
            if ($dow === 3 || $dow === 5) { // Wed or Fri
                if ($day['tp_a_code'] !== '-' || $day['tp_b_code'] !== '-') {
                    $marchErrors++;
                    echo "FAIL: Found TP shift on " . $day['date'] . " (Wed/Fri)\n";
                }

                $rsm = $day['rs_morning'] ?? '-';
                $rsa = $day['rs_afternoon'] ?? '-';

                if ($rsm === '-' || $rsa === '-') {
                    $marchErrors++;
                    echo "FAIL: Missing RS shift on " . $day['date'] . " (Wed/Fri)\n";
                }
            }
        }
    }
    if ($marchErrors === 0) {
        echo "PASS: March Wed/Fri logic is correct (TP closed, RS open).\n";
    } else {
        echo "FAIL: Found $marchErrors errors in March.\n";
    }

    // Verification 5: Check Dec 25-30 Exclusion
    echo "\n[Verification 5] Checking RS Exclusion (Dec 25-30, 2026)...\n";
    $exclusionErrors = 0;
    $checkDates = ['2026-12-25', '2026-12-26', '2026-12-27', '2026-12-28', '2026-12-29', '2026-12-30'];
    foreach ($calendar as $day) {
        $dateKey = DateTime::createFromFormat('d/m/Y', $day['date'])->format('Y-m-d');
        if (in_array($dateKey, $checkDates) && $day['is_working']) {
            $rsm = $day['rs_morning'] ?? '-';
            $rsa = $day['rs_afternoon'] ?? '-';
            if ($rsm !== '-' || $rsa !== '-') {
                $exclusionErrors++;
                echo "FAIL: Found RS shift on exclusion date $dateKey\n";
            }
        }
    }
    if ($exclusionErrors === 0) {
        echo "PASS: No RS shifts during Dec 25-30.\n";
    } else {
        echo "FAIL: Found $exclusionErrors shifts during exclusion period.\n";
    }

    // Verification 3: Check full month (January) - Wed/Fri should have TP
    echo "\n[Verification 3] Checking January (Full Month) Wed/Fri...\n";
    $janErrors = 0;
    foreach ($calendar as $day) {
        $date = DateTime::createFromFormat('d/m/Y', $day['date']);
        if ($date->format('n') == 1 && $day['is_working']) { // January
            $dow = (int) $date->format('w');
            if ($dow === 3 || $dow === 5) { // Wed or Fri
                if ($day['tp_a_code'] === '-' || $day['tp_b_code'] === '-') {
                    $janErrors++;
                    echo "FAIL: Missing TP shift on " . $day['date'] . " (Wed/Fri)\n";
                }
            }
        }
    }
    if ($janErrors === 0) {
        echo "PASS: January Wed/Fri logic is correct (TP open).\n";
    } else {
        echo "FAIL: Found $janErrors errors in January.\n";
    }

    // Verification 4: Check Group A Unavailability (Jan 9, 12-16, 19)
    // Unavailable: A1, A3, A4, A8, A9
    echo "\n[Verification 4] Checking Group A Unavailability...\n";
    $unavailableDates = ['2026-01-09', '2026-01-12', '2026-01-13', '2026-01-14', '2026-01-15', '2026-01-16', '2026-01-19'];
    $unavailableStaff = ['A1', 'A3', 'A4', 'A8', 'A9'];
    $groupAErrors = 0;

    foreach ($calendar as $day) {
        $dateKey = DateTime::createFromFormat('d/m/Y', $day['date'])->format('Y-m-d');
        if (in_array($dateKey, $unavailableDates)) {
            if (in_array($day['tp_a_code'], $unavailableStaff)) {
                $groupAErrors++;
                echo "FAIL: Assigned " . $day['tp_a_code'] . " on restricted date $dateKey\n";
            }
        }
    }

    if ($groupAErrors === 0) {
        echo "PASS: Group A unavailability constraints respected.\n";
    } else {
        echo "FAIL: Found $groupAErrors violations of Group A constraints.\n";
    }

    // 6. Check End of Year Exclusion (Dec 25, 28-30)
    echo "\n[Verification 6] Checking End of Year Exclusion (Dec 25, 28-30, 2026)...\n";
    $eoyExclusions = ['2026-12-25', '2026-12-28', '2026-12-29', '2026-12-30'];
    $eoyErrors = 0;
    foreach ($calendar as $day) {
        // Parse date to Y-m-d
        $dateParts = explode('/', $day['date']);
        if (count($dateParts) !== 3)
            continue;
        $ymd = sprintf("%04d-%02d-%02d", $dateParts[2], $dateParts[1], $dateParts[0]);

        if (in_array($ymd, $eoyExclusions)) {
            if ($day['is_working']) {
                echo "FAIL: $ymd has is_working=true\n";
                $eoyErrors++;
            }
            if ($day['tp_a'] !== '-' || $day['rs'] !== '-' || $day['rs_morning'] !== '-' || $day['rs_afternoon'] !== '-') {
                echo "FAIL: $ymd has assigned shifts.\n";
                $eoyErrors++;
            }
        }
    }
    if ($eoyErrors === 0) {
        echo "PASS: No shifts scheduled for Dec 25, 28-30.\n";
    } else {
        echo "FAIL: Found shifts during End of Year exclusion.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
