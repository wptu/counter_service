<?php
/**
 * Date Helper Utilities
 */

class DateHelper
{

    /**
     * Format date as yyyy-MM-dd
     */
    public static function formatDateKey(DateTime $date): string
    {
        return $date->format('Y-m-d');
    }

    /**
     * Format date as dd/MM/yyyy (Thai display format)
     */
    public static function formatDateDisplay(DateTime $date): string
    {
        return $date->format('d/m/Y');
    }

    /**
     * Format date as j F Y (Thai full date with Buddhist Era)
     * Example: 5 มกราคม 2569
     */
    public static function formatDateThaiFull(DateTime $date): string
    {
        $day = $date->format('j');
        $month = (int) $date->format('n') - 1;
        $year = (int) $date->format('Y') + 543;
        $thaiMonth = THAI_MONTHS[$month];

        return "$day $thaiMonth $year";
    }

    /**
     * Check if date is weekend (Saturday or Sunday)
     */
    public static function isWeekend(DateTime $date): bool
    {
        $dow = (int) $date->format('w'); // 0=Sunday, 6=Saturday
        return $dow === 0 || $dow === 6;
    }

    /**
     * Check if date is a Thai holiday
     */
    public static function isHoliday(DateTime $date, array $holidays): bool
    {
        $key = self::formatDateKey($date);
        return isset($holidays[$key]);
    }

    /**
     * Check if date is a special working day
     */
    public static function isSpecialWorkingDay(DateTime $date, array $specialDays): bool
    {
        $key = self::formatDateKey($date);
        return isset($specialDays[$key]);
    }

    /**
     * Check if date is a working day based on 2026 rules
     */
    public static function isWorkingDay(DateTime $date, array $holidays, array $specialDays): bool
    {
        // No work on weekends
        if (self::isWeekend($date)) {
            return false;
        }

        // Special working days count as working days
        if (self::isSpecialWorkingDay($date, $specialDays)) {
            return true;
        }

        // Month-based working day rules
        $month = (int) $date->format('n') - 1; // 0-indexed month
        $fullWeekMonths = FULL_WEEK_MONTHS;

        // Note: Wed and Fri in partial week months are now considered "Working Days"
        // but only for RS. TP will be closed.
        // So we return true here to allow scheduling engine to process the day.

        // Explicitly exclude Dec 25, 28-30, 2026 (No shifts at all)
        $dateKey = self::formatDateKey($date);
        $excludedDates = [
            '2026-12-25',
            '2026-12-28',
            '2026-12-29',
            '2026-12-30'
        ];
        if (in_array($dateKey, $excludedDates)) {
            return false;
        }

        // No work on holidays
        if (self::isHoliday($date, $holidays)) {
            return false;
        }

        return true;
    }

    /**
     * Check if TP office is working on this day
     */
    public static function isTpWorkingDay(DateTime $date): bool
    {
        $month = (int) $date->format('n') - 1; // 0-indexed month
        $fullWeekMonths = FULL_WEEK_MONTHS;

        // Full week months: TP works Mon-Fri
        if (in_array($month, $fullWeekMonths)) {
            return true;
        }

        // Partial week months: TP works Mon, Tue, Thu only
        $dow = (int) $date->format('w');
        if ($dow === 3 || $dow === 5) { // Wednesday or Friday
            return false;
        }

        return true;
    }

    /**
     * Get all working days in the year
     */
    public static function getWorkingDays(int $year, array $holidays, array $specialDays): array
    {
        $workingDays = [];
        $date = new DateTime("$year-01-01", new DateTimeZone(TIMEZONE));
        $endDate = new DateTime(($year + 1) . "-01-01", new DateTimeZone(TIMEZONE));

        while ($date < $endDate) {
            if (self::isWorkingDay($date, $holidays, $specialDays)) {
                $workingDays[] = clone $date;
            }
            $date->modify('+1 day');
        }

        return $workingDays;
    }

    /**
     * Get day of week name in Thai
     */
    public static function getThaiDayName(DateTime $date): string
    {
        $dow = (int) $date->format('w');
        return THAI_DAYS[$dow];
    }

    /**
     * Get month name in Thai
     */
    public static function getThaiMonthName(int $month): string
    {
        return THAI_MONTHS[$month];
    }

    /**
     * Calculate days between two dates
     */
    public static function daysBetween(DateTime $date1, DateTime $date2): int
    {
        $diff = $date2->getTimestamp() - $date1->getTimestamp();
        return (int) round($diff / 86400);
    }
}
