<?php
/**
 * TP (Office 1) Scheduler
 * Handles scheduling for TP office with Group A and Group B
 */

require_once __DIR__ . '/../models/Schedule.php';

class TpScheduler
{
    private Schedule $schedule;
    private array $groupA;
    private array $groupB;
    private array $lastAssignedA = [null, null];
    private array $lastAssignedB = [null, null];

    // Unavailable dates for Group A (A1, A3, A4, A8, A9)
    private array $unavailableA = [
        '2026-01-09' => ['A1', 'A3', 'A4', 'A8', 'A9'],
        '2026-01-12' => ['A1', 'A3', 'A4', 'A8', 'A9'],
        '2026-01-13' => ['A1', 'A3', 'A4', 'A8', 'A9'],
        '2026-01-14' => ['A1', 'A3', 'A4', 'A8', 'A9'],
        '2026-01-15' => ['A1', 'A3', 'A4', 'A8', 'A9'],
        '2026-01-16' => ['A1', 'A3', 'A4', 'A8', 'A9'],
        '2026-01-19' => ['A1', 'A3', 'A4', 'A8', 'A9'],
    ];

    public function __construct(Schedule $schedule, array $groupA, array $groupB)
    {
        $this->schedule = $schedule;
        $this->groupA = $groupA;
        $this->groupB = $groupB;
    }

    /**
     * Schedule TP shifts for all working days
     */
    public function scheduleTp(array $workingDays): void
    {
        foreach ($workingDays as $date) {
            $dow = (int) $date->format('w');
            $dateKey = DateHelper::formatDateKey($date);
            $month = (int) $date->format('n');

            // Check if TP is working today
            if (!DateHelper::isTpWorkingDay($date)) {
                // TP is closed
                $chosenA = '-';
                $chosenB = '-';
            } else {
                // Select Group A
                $chosenA = $this->selectGroupA($dow, $month, $dateKey);

                // Select Group B
                $day = (int) $date->format('j');
                $chosenB = $this->selectGroupB($dow, $month, $day);
            }

            // Update schedule
            $this->schedule->addShift($dateKey, [
                'date' => $dateKey,
                'day' => DateHelper::getThaiDayName($date),
                'tp_a' => $chosenA,
                'tp_b' => $chosenB,
                'rs' => null, // Will be filled by RS scheduler
                'holiday' => null,
                'remark' => null
            ]);

            // Update statistics
            if ($chosenA !== '-') {
                $this->schedule->updateStats($chosenA, $dow, true, $month);
            }
            if ($chosenB !== '-') {
                $this->schedule->updateStats($chosenB, $dow, true, $month);
            }

            // Update last assigned
            $this->lastAssignedA = [$chosenA, $this->lastAssignedA[0]];
            $this->lastAssignedB = [$chosenB, $this->lastAssignedB[0]];
        }
    }

    /**
     * Select staff from Group A using stable min selection
     */
    private function selectGroupA(int $dow, int $month, string $dateKey): string
    {
        // Find candidates (avoid last 2 assigned)
        $candidates = array_filter($this->groupA, function ($s) use ($dateKey) {
            // Check unavailability
            if (isset($this->unavailableA[$dateKey]) && in_array($s, $this->unavailableA[$dateKey])) {
                return false;
            }
            return !in_array($s, $this->lastAssignedA);
        });

        // Fallback if no candidates (relax previous assignments)
        if (empty($candidates)) {
            $candidates = array_filter($this->groupA, function ($s) use ($dateKey) {
                // Check unavailability even in fallback
                if (isset($this->unavailableA[$dateKey]) && in_array($s, $this->unavailableA[$dateKey])) {
                    return false;
                }
                return $s !== $this->lastAssignedA[0];
            });
        }

        if (empty($candidates)) {
            // Last resort: still must respect unavailability
            $candidates = array_filter($this->groupA, function ($s) use ($dateKey) {
                if (isset($this->unavailableA[$dateKey]) && in_array($s, $this->unavailableA[$dateKey])) {
                    return false;
                }
                return true;
            });
        }

        if (empty($candidates)) {
            throw new Exception("No Group A candidates available for $dateKey due to constraints.");
        }

        // Filter by monthly constraint (2-3 shifts/month preferred)
        $filteredCandidates = array_filter($candidates, function ($s) use ($month) {
            return $this->schedule->monthlyTpCounts[$s][$month] < 3;
        });

        if (empty($filteredCandidates)) {
            $filteredCandidates = $candidates;
        }

        // Select using stable min
        return $this->findMinStaff($filteredCandidates, function ($staff) use ($dow, $month) {
            return $this->scoreGroupA($staff, $dow, $month);
        });
    }

    /**
     * Calculate score for Group A selection
     */
    private function scoreGroupA(string $staff, int $dow, int $month): array
    {
        $monthlyCount = $this->schedule->monthlyTpCounts[$staff][$month];

        // Monthly penalty logic
        $monthlyPenalty = 0;
        if ($monthlyCount >= 3) {
            $monthlyPenalty = 1000;
        } elseif ($monthlyCount >= 2) {
            $monthlyPenalty = 10;
        } elseif ($monthlyCount === 1) {
            $monthlyPenalty = 0;
        } else {
            $monthlyPenalty = -5;
        }

        // Simulate adding this shift to calculate range
        $tempCounts = $this->schedule->dowCounts[$staff];
        $tempCounts[$dow]++;

        $range = max(array_values($tempCounts)) - min(array_values($tempCounts));
        $currentDow = $this->schedule->dowCounts[$staff][$dow];
        $total = $this->schedule->totalShifts[$staff];
        $minCount = min(array_values($tempCounts));

        // Return score tuple matching Python logic
        // Priority: Monthly > Total > Range > CurrentDow
        return [$monthlyPenalty, $total, $range, $currentDow, -$minCount, $staff];
    }

    /**
     * Select staff from Group B using stable min selection
     */
    private function selectGroupB(int $dow, int $month, int $day): string
    {
        // Find candidates (avoid last 2 assigned)
        $candidates = array_filter($this->groupB, function ($s) use ($month, $day) {
            // New Requirement: Exclude B12 in January (Month 1) and first week of Feb (Month 2, 1-7)
            if ($s === 'B12') {
                if ($month === 1)
                    return false;
                if ($month === 2 && $day <= 7)
                    return false;
            }
            return !in_array($s, $this->lastAssignedB);
        });

        // Fallback if no candidates
        if (empty($candidates)) {
            $candidates = array_filter($this->groupB, function ($s) {
                return $s !== $this->lastAssignedB[0];
            });
        }

        if (empty($candidates)) {
            $candidates = $this->groupB;
        }

        // Select using stable min
        return $this->findMinStaff($candidates, function ($staff) use ($dow, $month) {
            return $this->scoreGroupB($staff, $dow, $month);
        });
    }

    /**
     * Calculate score for Group B selection
     * Priority: monthly distribution > total balance > DoW balance
     */
    private function scoreGroupB(string $staff, int $dow, int $month): array
    {
        $monthlyCount = $this->schedule->monthlyTpCounts[$staff][$month];

        // Monthly penalty logic - CRITICAL for Group B
        $monthlyPenalty = 0;
        if ($monthlyCount === 0) {
            $monthlyPenalty = -5000; // MUST assign if not yet assigned this month
        } else {
            $monthlyPenalty = 2000; // Deprioritize if already assigned
        }

        // Simulate adding this shift
        $tempCounts = $this->schedule->dowCounts[$staff];
        $tempCounts[$dow]++;

        $range = max(array_values($tempCounts)) - min(array_values($tempCounts));
        $currentDow = $this->schedule->dowCounts[$staff][$dow];
        $total = $this->schedule->totalShifts[$staff];
        $minCount = min(array_values($tempCounts));

        // Return score tuple matching Python logic
        // Priority: monthly > total > range > currentDow
        return [$monthlyPenalty, $total, $range, $currentDow, -$minCount, $staff];
    }

    /**
     * Find staff with minimum score (stable selection like Python min())
     */
    private function findMinStaff(array $candidates, callable $scoreFunc): string
    {
        if (empty($candidates)) {
            throw new Exception("No candidates available for selection");
        }

        // Reset array keys to ensure deterministic iteration
        $candidates = array_values($candidates);

        $bestStaff = $candidates[0];
        $bestScore = $scoreFunc($bestStaff);

        for ($i = 1; $i < count($candidates); $i++) {
            $currentStaff = $candidates[$i];
            $currentScore = $scoreFunc($currentStaff);

            if ($this->compareScore($currentScore, $bestScore) < 0) {
                $bestStaff = $currentStaff;
                $bestScore = $currentScore;
            }
        }

        return $bestStaff;
    }

    /**
     * Compare two score arrays (stable comparison like Python tuple comparison)
     */
    private function compareScore(array $scoreA, array $scoreB): int
    {
        $len = min(count($scoreA), count($scoreB));

        for ($i = 0; $i < $len; $i++) {
            if ($scoreA[$i] < $scoreB[$i]) {
                return -1;
            } elseif ($scoreA[$i] > $scoreB[$i]) {
                return 1;
            }
        }

        return 0;
    }
}
