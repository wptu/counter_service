<?php
/**
 * Schedule Database Model
 */

require_once __DIR__ . '/../config/database.php';

class ScheduleDB
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Save full schedule to database
     */
    public function saveSchedule(array $calendar, int $year): bool
    {
        try {
            $this->db->beginTransaction();

            // Clear existing schedule for this year
            $this->clearSchedule($year);

            // Prepare statement
            $stmt = $this->db->prepare("
                INSERT INTO schedules 
                (year, date, day_name, tp_a_id, tp_b_id, rs_id, rs_morning_id, rs_afternoon_id, is_working, is_weekend, is_holiday, holiday_name, remark)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($calendar as $day) {
                // Convert date from dd/mm/yyyy to yyyy-mm-dd
                $dateParts = explode('/', $day['date']);
                if (count($dateParts) === 3) {
                    $sqlDate = sprintf("%04d-%02d-%02d", $dateParts[2], $dateParts[1], $dateParts[0]);
                } else {
                    continue; // Skip invalid dates
                }

                // Get staff IDs from codes
                $tpAId = isset($day['tp_a_code']) ? $this->getStaffId($day['tp_a_code']) : null;
                $tpBId = isset($day['tp_b_code']) ? $this->getStaffId($day['tp_b_code']) : null;
                // Use codes if available, otherwise rs_code logic might be flawed if composite
                // We stored composite in rs_code in RsScheduler.php? 
                // No, RsScheduler sets rs_morning and rs_afternoon.
                // rs_code might be set in Scheduler.php?
                // Let's rely on explicit keys if they passed through

                $rsMorningId = (isset($day['rs_morning_code']) && $day['rs_morning_code'] !== '-') ? $this->getStaffId($day['rs_morning_code']) : null;
                $rsAfternoonId = (isset($day['rs_afternoon_code']) && $day['rs_afternoon_code'] !== '-') ? $this->getStaffId($day['rs_afternoon_code']) : null;

                // Legacy rs_id (first part of composite or null)
                $rsId = isset($day['rs_code']) && strpos($day['rs_code'], '/') === false ? $this->getStaffId($day['rs_code']) : null;


                $stmt->execute([
                    $year,
                    $sqlDate,
                    $day['day'],
                    $tpAId,
                    $tpBId,
                    $rsId,
                    $rsMorningId,
                    $rsAfternoonId,
                    $day['is_working'] ? 1 : 0,
                    $day['is_weekend'] ? 1 : 0,
                    $day['is_holiday'] ? 1 : 0,
                    $day['holiday'] !== '-' ? $day['holiday'] : null,
                    $day['remark']
                ]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error saving schedule: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save schedule metadata
     */
    public function saveMeta(int $year, int $workingDaysCount, array $rsGroupCounts): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO schedule_meta (year, working_days_count, rs_group_a_count, rs_group_b_count)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    working_days_count = VALUES(working_days_count),
                    rs_group_a_count = VALUES(rs_group_a_count),
                    rs_group_b_count = VALUES(rs_group_b_count),
                    generated_at = CURRENT_TIMESTAMP
            ");

            return $stmt->execute([
                $year,
                $workingDaysCount,
                $rsGroupCounts['A'],
                $rsGroupCounts['B']
            ]);

        } catch (Exception $e) {
            error_log("Error saving meta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get schedule for year
     */
    public function getScheduleByYear(int $year): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                s.*,
                sa.code as tp_a_code, sa.name as tp_a_name,
                sb.code as tp_b_code, sb.name as tp_b_name,
                sr.code as rs_code, sr.name as rs_name,
                srm.code as rs_morning_code, srm.name as rs_morning_name,
                sra.code as rs_afternoon_code, sra.name as rs_afternoon_name
            FROM schedules s
            LEFT JOIN staff sa ON s.tp_a_id = sa.id
            LEFT JOIN staff sb ON s.tp_b_id = sb.id
            LEFT JOIN staff sr ON s.rs_id = sr.id
            LEFT JOIN staff srm ON s.rs_morning_id = srm.id
            LEFT JOIN staff sra ON s.rs_afternoon_id = sra.id
            WHERE s.year = ?
            ORDER BY s.date
        ");
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    }

    /**
     * Get schedule metadata
     */
    public function getMeta(int $year): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM schedule_meta WHERE year = ?");
        $stmt->execute([$year]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Clear schedule for year
     */
    public function clearSchedule(int $year): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM schedules WHERE year = ?");
            $stmt->execute([$year]);

            $stmt = $this->db->prepare("DELETE FROM schedule_meta WHERE year = ?");
            $stmt->execute([$year]);

            return true;
        } catch (Exception $e) {
            error_log("Error clearing schedule: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if schedule exists for year
     */
    public function scheduleExists(int $year): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM schedules WHERE year = ?");
        $stmt->execute([$year]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get staff ID by code
     */
    private function getStaffId(string $code): ?int
    {
        $stmt = $this->db->prepare("SELECT id FROM staff WHERE code = ?");
        $stmt->execute([$code]);
        $result = $stmt->fetchColumn();
        return $result !== false ? (int) $result : null;
    }

    /**
     * Get shifts for specific staff member
     */
    public function getShiftsByStaff(int $staffId, int $year): array
    {
        $stmt = $this->db->prepare("
            SELECT date, day_name,
                   CASE 
                       WHEN tp_a_id = ? THEN 'ทพ.'
                       WHEN tp_b_id = ? THEN 'ทพ.'
                       WHEN rs_id = ? THEN 'รส.'
                   END as shift_type
            FROM schedules
            WHERE year = ? AND (tp_a_id = ? OR tp_b_id = ? OR rs_id = ?)
            ORDER BY date
        ");
        $stmt->execute([$staffId, $staffId, $staffId, $year, $staffId, $staffId, $staffId]);
        return $stmt->fetchAll();
    }
}
