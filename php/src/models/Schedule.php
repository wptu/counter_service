<?php
/**
 * Schedule Data Structure
 */

class Schedule
{
    public array $schedules = [];
    public array $totalShifts = [];
    public array $dowCounts = [];
    public array $tpCounts = [];
    public array $rsCounts = [];
    public array $rsMorningCounts = [];
    public array $rsAfternoonCounts = [];
    public array $monthlyTpCounts = [];
    public array $rsGroupCounts = ['A' => 0, 'B' => 0];

    public function __construct(array $allStaff)
    {
        // Initialize statistics for all staff
        foreach ($allStaff as $staff) {
            $this->totalShifts[$staff] = 0;
            $this->tpCounts[$staff] = 0;
            $this->rsCounts[$staff] = 0;
            $this->rsMorningCounts[$staff] = 0;
            $this->rsAfternoonCounts[$staff] = 0;
            $this->dowCounts[$staff] = [
                1 => 0, // Monday
                2 => 0, // Tuesday
                3 => 0, // Wednesday
                4 => 0, // Thursday
                5 => 0  // Friday
            ];

            // Initialize monthly TP counts
            $this->monthlyTpCounts[$staff] = [];
            for ($m = 1; $m <= 12; $m++) {
                $this->monthlyTpCounts[$staff][$m] = 0;
            }
        }
    }

    public function addShift(string $dateKey, array $shift): void
    {
        $this->schedules[$dateKey] = $shift;
    }

    public function getShift(string $dateKey): ?array
    {
        return $this->schedules[$dateKey] ?? null;
    }

    public function getAllSchedules(): array
    {
        return $this->schedules;
    }

    public function updateStats(string $staff, int $dow, bool $isTp = true, ?int $month = null, ?string $rsShiftType = null): void
    {
        $this->totalShifts[$staff]++;
        $this->dowCounts[$staff][$dow]++;

        if ($isTp) {
            $this->tpCounts[$staff]++;
            if ($month !== null) {
                $this->monthlyTpCounts[$staff][$month]++;
            }
        } else {
            $this->rsCounts[$staff]++;
            if ($rsShiftType === 'morning') {
                $this->rsMorningCounts[$staff]++;
            } elseif ($rsShiftType === 'afternoon') {
                $this->rsAfternoonCounts[$staff]++;
            }
        }
    }

    public function updateRsGroupCount(string $group): void
    {
        $this->rsGroupCounts[$group]++;
    }

    public function getDowRange(string $staff): int
    {
        $counts = array_values($this->dowCounts[$staff]);
        return max($counts) - min($counts);
    }

    public function getMinDowCount(string $staff): int
    {
        return min(array_values($this->dowCounts[$staff]));
    }
}
