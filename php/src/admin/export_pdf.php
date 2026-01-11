<?php
/**
 * Admin Export Schedule to PDF (Grid Layout)
 */

session_start();

require_once __DIR__ . '/../auth/Auth.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../scheduling/Scheduler.php';
require_once __DIR__ . '/../models/ScheduleDB.php';

// Require admin login
Auth::requireAdmin();

$year = isset($_GET['year']) ? (int) $_GET['year'] : YEAR;
$month = isset($_GET['month']) ? (int) $_GET['month'] : 0;

if ($month < 1 || $month > 12) {
    die("Invalid month");
}

// Initialize
$scheduler = new Scheduler();
$calendar = $scheduler->loadScheduleFromDB();

if ($calendar === null) {
    die("Schedule not found for year $year");
}

// Filter by month and organize by date
$monthData = [];
foreach ($calendar as $day) {
    $dateParts = explode('/', $day['date']);
    $m = isset($dateParts[1]) ? (int) $dateParts[1] : 0;
    $d = isset($dateParts[0]) ? (int) $dateParts[0] : 0;

    if ($m === $month) {
        $monthData[$d] = $day;
    }
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

// Calculate grid
// Create a date object for the 1st of the month
$firstDateStr = sprintf("%d-%02d-01", $year, $month);
$firstDate = new DateTime($firstDateStr);
$startDow = (int) $firstDate->format('w'); // 0 for Sunday, 6 for Saturday

// Last day
$lastDate = clone $firstDate;
$lastDate->modify('last day of this month');
$lastDayNum = (int) $lastDate->format('d');

$thaiDays = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ตารางเวรเดือน<?= $thaiMonths[$month] ?> <?= $year + 543 ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            margin: 0;
            padding: 10px;
            background: white;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        h1 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }

        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Force equal widths */
        }

        .calendar-table th {
            background-color: #95a5a6;
            /* Grey header */
            color: white;
            padding: 8px;
            text-align: center;
            border: 1px solid #bdc3c7;
            font-weight: bold;
            font-size: 16px;
        }

        .calendar-table td {
            height: 120px;
            /* Fixed height for cells */
            vertical-align: top;
            border: 1px solid #bdc3c7;
            padding: 5px;
            font-size: 12px;
            position: relative;
        }

        /* Weekend Styling */
        .col-sun,
        .col-sat {
            background-color: #fffde7;
            /* Light yellow for weekends */
        }

        /* Holiday Styling Overlay */
        .holiday-cell {
            background-color: #ffebee !important;
            /* Pink for holidays */
        }

        .date-number {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 5px;
            display: inline-block;
        }

        .shift-info {
            line-height: 1.4;
        }

        .label {
            font-weight: bold;
            color: #2c3e50;
        }

        .holiday-text {
            color: #c0392b;
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }

        @media print {
            body {
                padding: 0;
            }

            @page {
                margin: 0.5cm;
                size: A4 landscape;
            }

            .no-print {
                display: none;
            }

            /* Force background colors */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">🖨️ Print / Save as PDF</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">❌ Close</button>
    </div>

    <div class="header">
        <h1>ตารางเวร เดือน<?= $thaiMonths[$month] ?> พ.ศ. <?= $year + 543 ?></h1>
    </div>

    <table class="calendar-table">
        <thead>
            <tr>
                <?php foreach ($thaiDays as $day): ?>
                    <th><?= $day ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <tr>
                <?php
                // Padding for first week
                for ($i = 0; $i < $startDow; $i++) {
                    // Determine if the empty cell is a weekend column
                    $bgClass = ($i === 0 || $i === 6) ? 'col-sun' : '';
                    if ($i === 6)
                        $bgClass = 'col-sat';
                    echo "<td class='$bgClass'></td>";
                }

                // Days
                $currentDow = $startDow;
                for ($d = 1; $d <= $lastDayNum; $d++) {
                    if ($currentDow > 6) {
                        $currentDow = 0;
                        echo "</tr><tr>";
                    }

                    $dayData = isset($monthData[$d]) ? $monthData[$d] : null;

                    // Determine Classes
                    $classes = [];
                    if ($currentDow === 0)
                        $classes[] = 'col-sun';
                    if ($currentDow === 6)
                        $classes[] = 'col-sat';
                    if ($dayData && $dayData['is_holiday'])
                        $classes[] = 'holiday-cell';

                    $classStr = implode(' ', $classes);

                    echo "<td class='$classStr'>";
                    echo "<div class='date-number'>$d</div>";

                    if ($dayData) {
                        if ($dayData['is_holiday']) {
                            echo "<div class='holiday-text'>" . htmlspecialchars($dayData['holiday']) . "</div>";
                        } elseif ($dayData['is_working']) {
                            echo "<div class='shift-info'>";

                            // TP
                            $tpList = [];
                            if ($dayData['tp_a'] !== '-')
                                $tpList[] = $dayData['tp_a'];
                            if ($dayData['tp_b'] !== '-')
                                $tpList[] = $dayData['tp_b'];
                            if (!empty($tpList)) {
                                echo "<div><span class='label'>ทพ.:</span> " . implode(', ', $tpList) . "</div>";
                            }

                            // RS
                            if ($dayData['rs'] !== '-') {
                                echo "<div><span class='label'>รส.:</span> " . $dayData['rs'] . "</div>";
                            }

                            echo "</div>";
                        }
                    }

                    echo "</td>";

                    $currentDow++;
                }

                // Padding for last week
                while ($currentDow <= 6) {
                    $bgClass = ($currentDow === 0 || $currentDow === 6) ? 'col-sun' : ''; // simplified, actually only Sat left usually
                    if ($currentDow === 6)
                        $bgClass = 'col-sat';
                    echo "<td class='$bgClass'></td>";
                    $currentDow++;
                }
                ?>
            </tr>
        </tbody>
    </table>

</body>

</html>