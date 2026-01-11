<?php
/**
 * RS (Office 2) Scheduler
 * Handles scheduling for RS office with progressive fallback logic
 */

require_once __DIR__ . '/../models/Schedule.php';

class RsScheduler
{
    private Schedule $schedule;
    private array $allStaff;
    private array $groupA;
    private array $groupB;
    private array $workingDays;

    public function __construct(Schedule $schedule, array $allStaff, array $groupA, array $groupB, array $workingDays)
    {
        $this->schedule = $schedule;
        $this->allStaff = $allStaff;
        $this->groupA = $groupA;
        $this->groupB = $groupB;
        $this->workingDays = $workingDays;
    }

    /**
     * Schedule RS shifts for all working days
     */
    /**
     * Schedule RS shifts for all working days
     */
    public function scheduleRs(): void
    {
        $totalDays = count($this->workingDays);
        $exclusionStart = '2026-12-25';
        $exclusionEnd = '2026-12-30';

        foreach ($this->workingDays as $idx => $date) {
            $dow = (int) $date->format('w');
            $dateKey = DateHelper::formatDateKey($date);
            $dateFormatted = $date->format('Y-m-d');

            // Check exclusion dates
            if ($dateFormatted >= $exclusionStart && $dateFormatted <= $exclusionEnd) {
                // No RS shifts
                $shift = $this->schedule->getShift($dateKey);
                $shift['rs_morning'] = '-';
                $shift['rs_afternoon'] = '-';
                $shift['rs'] = '-'; // Legacy support
                $shift['remark'] = ($shift['remark'] !== '-' ? $shift['remark'] . '; ' : '') . 'งดเวร RS (25-30 ธ.ค.)';
                $this->schedule->addShift($dateKey, $shift);
                continue;
            }

            // Get current TP assignment
            $shift = $this->schedule->getShift($dateKey);
            $tpA = $shift['tp_a'];
            $tpB = $shift['tp_b'];

            // --- Schedule Morning Shift ---
            $resultMorning = $this->findRsCandidates($idx, $tpA, $tpB, []); // No exclusions yet
            $candidatesM = $resultMorning['candidates'];
            $fallbackM = $resultMorning['fallback_level'];

            if (empty($candidatesM)) {
                throw new Exception("No RS Morning candidates found for date: $dateKey");
            }

            // Select best candidate for Morning
            // Prefer balanced total shifts AND balanced morning shifts
            $rsMorning = $this->findMinStaff($candidatesM, function ($staff) use ($dow) {
                return $this->scoreRs($staff, $dow, 'morning');
            });

            // Update stats
            $this->schedule->updateStats($rsMorning, $dow, false, null, 'morning');


            // --- Schedule Afternoon Shift ---
            $resultAfternoon = $this->findRsCandidates($idx, $tpA, $tpB, [$rsMorning]); // Exclude morning person
            $candidatesA = $resultAfternoon['candidates'];
            $fallbackA = $resultAfternoon['fallback_level'];

            if (empty($candidatesA)) {
                // If really stuck, maybe allow morning person to double? (Unlikely based on rules, best strict)
                // Let's stick to strict rules for now.
                throw new Exception("No RS Afternoon candidates found for date: $dateKey");
            }

            // Select best candidate for Afternoon
            $rsAfternoon = $this->findMinStaff($candidatesA, function ($staff) use ($dow) {
                return $this->scoreRs($staff, $dow, 'afternoon');
            });

            // Update stats
            $this->schedule->updateStats($rsAfternoon, $dow, false, null, 'afternoon');

            // Build reason
            $reason = "เช้า: $rsMorning, บ่าย: $rsAfternoon";
            if ($fallbackM > 0)
                $reason .= " [M level $fallbackM]";
            if ($fallbackA > 0)
                $reason .= " [A level $fallbackA]";

            // Update shift
            $shift['rs_morning'] = $rsMorning;
            $shift['rs_afternoon'] = $rsAfternoon;
            $shift['rs'] = "$rsMorning/$rsAfternoon"; // store composite for legacy

            // Append remark
            if ($shift['remark'] === '-') {
                $shift['remark'] = $reason;
            } else {
                $shift['remark'] .= " | $reason";
            }

            $this->schedule->addShift($dateKey, $shift);

            // Update legacy group counts (just simplistic)
            // We don't really rely on A/B ratio anymore but let's keep tracking
            $groupM = in_array($rsMorning, $this->groupA) ? 'A' : 'B';
            $groupA = in_array($rsAfternoon, $this->groupA) ? 'A' : 'B';
            $this->schedule->updateRsGroupCount($groupM);
            $this->schedule->updateRsGroupCount($groupA);
        }
    }

    /**
     * Find RS candidates with progressive fallback
     * @param array $currentExcludees Staff to exclude specifically (e.g. morning person for afternoon shift)
     */
    private function findRsCandidates(int $idx, string $tpA, string $tpB, array $currentExcludees): array
    {
        $currentDate = $this->workingDays[$idx];
        $month = (int) $currentDate->format('n');
        $day = (int) $currentDate->format('j');

        $candidates = [];
        $fallbackLevel = 0;

        // Level 0: Full constraints
        foreach ($this->allStaff as $s) {
            // Basic Exclusions
            if ($s === $tpA || $s === $tpB)
                continue;
            if (in_array($s, $currentExcludees))
                continue;

            // Exclude B12 in January and Feb 1-7
            if ($s === 'B12') {
                if ($month === 1)
                    continue;
                if ($month === 2 && $day <= 7)
                    continue;
            }

            // Cannot work previous working day (Morning or Afternoon)
            if ($idx > 0) {
                $prevDate = $this->workingDays[$idx - 1];
                $prevKey = DateHelper::formatDateKey($prevDate);
                $prevShift = $this->schedule->getShift($prevKey);
                // prev RS might be "A/B" or array? It's array in my new logic, but read as string in loop? 
                // Wait, getShift returns current state array.
                // I need to check against rs_morning and rs_afternoon if set.
                $prevM = $prevShift['rs_morning'] ?? '-';
                $prevA = $prevShift['rs_afternoon'] ?? '-';
                // Also check legacy 'rs' just in case? No, I control it.

                if ($s === $prevShift['tp_a'] || $s === $prevShift['tp_b'] || $s === $prevM || $s === $prevA) {
                    continue;
                }
            }

            // Cannot have TP shift next working day
            if ($idx < count($this->workingDays) - 1) {
                $nextDate = $this->workingDays[$idx + 1];
                $nextKey = DateHelper::formatDateKey($nextDate);
                $nextShift = $this->schedule->getShift($nextKey);
                if ($s === $nextShift['tp_a'] || $s === $nextShift['tp_b']) {
                    continue;
                }
            }

            $candidates[] = $s;
        }

        // Level 1: Relax previous day constraint
        if (empty($candidates)) {
            $fallbackLevel = 1;
            foreach ($this->allStaff as $s) {
                if ($s === $tpA || $s === $tpB)
                    continue;
                if (in_array($s, $currentExcludees))
                    continue;

                if ($s === 'B12') {
                    if ($month === 1)
                        continue;
                    if ($month === 2 && $day <= 7)
                        continue;
                }

                // Still check next day TP constraint
                if ($idx < count($this->workingDays) - 1) {
                    $nextDate = $this->workingDays[$idx + 1];
                    $nextKey = DateHelper::formatDateKey($nextDate);
                    $nextShift = $this->schedule->getShift($nextKey);
                    if ($s === $nextShift['tp_a'] || $s === $nextShift['tp_b']) {
                        continue;
                    }
                }
                $candidates[] = $s;
            }
        }

        // Level 2: Only avoid TP today and immediate conflicts
        if (empty($candidates)) {
            $fallbackLevel = 2;
            foreach ($this->allStaff as $s) {
                if ($s === 'B12') {
                    if ($month === 1)
                        continue;
                    if ($month === 2 && $day <= 7)
                        continue;
                }
                if ($s !== $tpA && $s !== $tpB && !in_array($s, $currentExcludees)) {
                    $candidates[] = $s;
                }
            }
        }

        return [
            'candidates' => $candidates,
            'fallback_level' => $fallbackLevel
        ];
    }

    /**
     * Calculate score for RS selection
     * Priority: 1. Total Shifts, 2. Shift Type Balance, 3. DOW Balance
     */
    private function scoreRs(string $staff, int $dow, string $shiftType): array
    {
        // 1. Total Shifts (Global Balance)
        $total = $this->schedule->totalShifts[$staff];

        // 2. Shift Type Balance (Morning vs Afternoon specific to this person)
        // We want everyone to have roughly equal Morning and Afternoon shifts relative to others
        // actually, user said "Equality - Shift Type".
        $typeCount = ($shiftType === 'morning')
            ? $this->schedule->rsMorningCounts[$staff]
            : $this->schedule->rsAfternoonCounts[$staff];

        // 3. DOW Balance
        $tempCounts = $this->schedule->dowCounts[$staff];
        $tempCounts[$dow]++;
        $range = max(array_values($tempCounts)) - min(array_values($tempCounts));
        $rangePenalty = max(0, $range - 2) * 5; // Reduced penalty

        return [$total, $typeCount, $range + $rangePenalty, $staff];
    }

    /**
     * Find staff with minimum score (stable selection)
     */
    private function findMinStaff(array $candidates, callable $scoreFunc): string
    {
        if (empty($candidates)) {
            throw new Exception("No candidates available for RS selection");
        }

        // Reset array keys for deterministic iteration
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
     * Compare two score arrays (stable comparison)
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
