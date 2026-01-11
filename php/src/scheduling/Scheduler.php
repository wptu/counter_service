<?php
/**
 * Main Scheduler - Orchestrates TP and RS scheduling
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/holidays.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/DateHelper.php';
require_once __DIR__ . '/../models/Staff.php';
require_once __DIR__ . '/../models/Schedule.php';
require_once __DIR__ . '/../models/ScheduleDB.php';
require_once __DIR__ . '/TpScheduler.php';
require_once __DIR__ . '/RsScheduler.php';

class Scheduler
{
    private Staff $staff;
    private Schedule $schedule;
    private ScheduleDB $scheduleDB;
    private array $holidays;
    private array $specialDays;
    private array $workingDays;

    public function __construct()
    {
        // Load configurations
        $holidayConfig = require __DIR__ . '/../config/holidays.php';
        $this->holidays = $holidayConfig['holidays'];
        $this->specialDays = $holidayConfig['special_working_days'];

        // Initialize staff from database
        $this->staff = new Staff();

        // Initialize schedule
        $this->schedule = new Schedule($this->staff->getAllStaff());

        // Initialize schedule DB
        $this->scheduleDB = new ScheduleDB();

        // Calculate working days
        $this->workingDays = DateHelper::getWorkingDays(YEAR, $this->holidays, $this->specialDays);
    }

    /**
     * Generate full schedule for the year
     */
    public function generateSchedule(): array
    {
        // Step 1: Schedule TP (Office 1)
        $tpScheduler = new TpScheduler(
            $this->schedule,
            $this->staff->getGroupA(),
            $this->staff->getGroupB()
        );
        $tpScheduler->scheduleTp($this->workingDays);

        // Step 2: Schedule RS (Office 2)
        $rsScheduler = new RsScheduler(
            $this->schedule,
            $this->staff->getAllStaff(),
            $this->staff->getGroupA(),
            $this->staff->getGroupB(),
            $this->workingDays
        );
        $rsScheduler->scheduleRs();

        // Build full year calendar (including non-working days)
        $calendar = $this->buildFullCalendar();

        // Save to database
        $this->scheduleDB->saveSchedule($calendar, YEAR);
        $this->scheduleDB->saveMeta(
            YEAR,
            count($this->workingDays),
            $this->schedule->rsGroupCounts
        );

        return $calendar;
    }

    /**
     * Load schedule from database
     */
    public function loadScheduleFromDB(): ?array
    {
        if (!$this->scheduleDB->scheduleExists(YEAR)) {
            return null;
        }

        $dbSchedule = $this->scheduleDB->getScheduleByYear(YEAR);
        $calendar = [];

        foreach ($dbSchedule as $row) {
            // Convert MySQL date (yyyy-mm-dd) to dd/mm/yyyy
            $date = DateTime::createFromFormat('Y-m-d', $row['date']);
            $dateDisplay = $date->format('d/m/Y');

            $calendar[] = [
                'date' => $dateDisplay,
                'day' => $row['day_name'],
                'tp_a' => $row['tp_a_name'] ?? '-',
                'tp_b' => $row['tp_b_name'] ?? '-',
                'rs' => $row['rs_name'] ?? ($row['rs_code'] ?? '-'),
                'rs_morning' => $row['rs_morning_name'] ?? ($row['rs_morning_code'] ?? '-'),
                'rs_afternoon' => $row['rs_afternoon_name'] ?? ($row['rs_afternoon_code'] ?? '-'),
                'holiday' => $row['holiday_name'] ?? '-',
                'remark' => $row['remark'] ?? '-',
                'is_working' => (bool) $row['is_working'],
                'is_weekend' => (bool) $row['is_weekend'],
                'is_holiday' => (bool) $row['is_holiday'],
                'tp_a_code' => $row['tp_a_code'],
                'tp_b_code' => $row['tp_b_code'],
                'rs_code' => $row['rs_code'],
                'rs_morning_code' => $row['rs_morning_code'],
                'rs_afternoon_code' => $row['rs_afternoon_code']
            ];
        }

        return $calendar;
    }

    /**
     * Build full calendar for the year including weekends and holidays
     */
    private function buildFullCalendar(): array
    {
        $calendar = [];
        $date = new DateTime(YEAR . "-01-01", new DateTimeZone(TIMEZONE));
        $endDate = new DateTime((YEAR + 1) . "-01-01", new DateTimeZone(TIMEZONE));

        while ($date < $endDate) {
            $dateKey = DateHelper::formatDateKey($date);
            $dayName = DateHelper::getThaiDayName($date);
            $dateDisplay = DateHelper::formatDateDisplay($date);

            if (DateHelper::isWeekend($date)) {
                // Weekend
                $calendar[] = [
                    'date' => $dateDisplay,
                    'day' => $dayName,
                    'tp_a' => '-',
                    'tp_b' => '-',
                    'rs' => '-',
                    'rs_morning' => '-',
                    'rs_afternoon' => '-',
                    'holiday' => '-',
                    'remark' => 'เสาร์-อาทิตย์ (ไม่จัดเวร)',
                    'is_working' => false,
                    'is_weekend' => true,
                    'is_holiday' => false,
                    'tp_a_code' => null,
                    'tp_b_code' => null,
                    'rs_code' => null,
                    'rs_morning_code' => null,
                    'rs_afternoon_code' => null
                ];
            } elseif (DateHelper::isHoliday($date, $this->holidays)) {
                // Holiday
                $holidayName = $this->holidays[$dateKey];
                $calendar[] = [
                    'date' => $dateDisplay,
                    'day' => $dayName,
                    'tp_a' => '-',
                    'tp_b' => '-',
                    'rs' => '-',
                    'rs_morning' => '-',
                    'rs_afternoon' => '-',
                    'holiday' => $holidayName,
                    'remark' => 'วันหยุดราชการ (ไม่จัดเวร)',
                    'is_working' => false,
                    'is_weekend' => false,
                    'is_holiday' => true,
                    'tp_a_code' => null,
                    'tp_b_code' => null,
                    'rs_code' => null,
                    'rs_morning_code' => null,
                    'rs_afternoon_code' => null
                ];
            } elseif (DateHelper::isWorkingDay($date, $this->holidays, $this->specialDays)) {
                // Working day
                $shift = $this->schedule->getShift($dateKey);

                $remark = $shift['remark'] ?? '-';
                if (DateHelper::isSpecialWorkingDay($date, $this->specialDays)) {
                    $specialNote = $this->specialDays[$dateKey];
                    $remark = $specialNote . ' | ' . $remark;
                }

                $calendar[] = [
                    'date' => $dateDisplay,
                    'day' => $dayName,
                    'tp_a' => $this->staff->getDisplayName($shift['tp_a']),
                    'tp_b' => $this->staff->getDisplayName($shift['tp_b']),
                    'rs' => $this->staff->getDisplayName($shift['rs']), // Should match stored
                    'rs_morning' => isset($shift['rs_morning']) ? $this->staff->getDisplayName($shift['rs_morning']) : '-',
                    'rs_afternoon' => isset($shift['rs_afternoon']) ? $this->staff->getDisplayName($shift['rs_afternoon']) : '-',
                    'holiday' => '-',
                    'remark' => $remark,
                    'is_working' => true,
                    'is_weekend' => false,
                    'is_holiday' => false,
                    'tp_a_code' => $shift['tp_a'],
                    'tp_b_code' => $shift['tp_b'],
                    'rs_code' => $shift['rs'] ?? null,
                    'rs_morning_code' => $shift['rs_morning'] ?? null,
                    'rs_afternoon_code' => $shift['rs_afternoon'] ?? null
                ];
            } else {
                // Other non-working day
                $calendar[] = [
                    'date' => $dateDisplay,
                    'day' => $dayName,
                    'tp_a' => '-',
                    'tp_b' => '-',
                    'rs' => '-',
                    'rs_morning' => '-',
                    'rs_afternoon' => '-',
                    'holiday' => '-',
                    'remark' => '(ไม่คาดคิด)',
                    'is_working' => false,
                    'is_weekend' => false,
                    'is_holiday' => false,
                    'tp_a_code' => null,
                    'tp_b_code' => null,
                    'rs_code' => null,
                    'rs_morning_code' => null,
                    'rs_afternoon_code' => null
                ];
            }

            $date->modify('+1 day');
        }

        return $calendar;
    }

    /**
     * Get working days count
     */
    public function getWorkingDaysCount(): int
    {
        return count($this->workingDays);
    }

    /**
     * Get RS group distribution
     */
    public function getRsGroupCounts(): array
    {
        return $this->schedule->rsGroupCounts;
    }

    /**
     * Get schedule statistics
     */
    public function getStats(): array
    {
        return [
            'total_shifts' => $this->schedule->totalShifts,
            'tp_counts' => $this->schedule->tpCounts,
            'rs_counts' => $this->schedule->rsCounts,
            'dow_counts' => $this->schedule->dowCounts,
            'monthly_tp_counts' => $this->schedule->monthlyTpCounts
        ];
    }

    /**
     * Get staff object
     */
    public function getStaff(): Staff
    {
        return $this->staff;
    }
}
