<?php
/**
 * Staff Management (Database-backed)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/StaffDB.php';

class Staff
{
    private array $groupA = [];
    private array $groupB = [];
    private array $allStaff = [];
    private array $staffNames = [];
    private StaffDB $staffDB;

    public function __construct()
    {
        $this->staffDB = new StaffDB();
        $this->loadStaffFromDB();
    }

    private function loadStaffFromDB(): void
    {
        $allStaffData = $this->staffDB->getAll();

        foreach ($allStaffData as $staff) {
            $code = $staff['code'];
            $name = $staff['name'];
            $group = $staff['group_type'];

            if ($group === 'A') {
                $this->groupA[] = $code;
            } else {
                $this->groupB[] = $code;
            }

            $this->staffNames[$code] = $name;
        }

        // Already sorted by database query (natural sort)
        $this->allStaff = array_merge($this->groupA, $this->groupB);
    }

    public function getGroupA(): array
    {
        return $this->groupA;
    }

    public function getGroupB(): array
    {
        return $this->groupB;
    }

    public function getAllStaff(): array
    {
        return $this->allStaff;
    }

    public function getDisplayName(string $code): string
    {
        return $this->staffNames[$code] ?? $code;
    }

    public function getGroup(string $code): string
    {
        if (in_array($code, $this->groupA)) {
            return 'A';
        } elseif (in_array($code, $this->groupB)) {
            return 'B';
        }
        return 'Unknown';
    }

    public function getStaffNames(): array
    {
        return $this->staffNames;
    }

    /**
     * Refresh staff data from database
     */
    public function refresh(): void
    {
        $this->groupA = [];
        $this->groupB = [];
        $this->allStaff = [];
        $this->staffNames = [];
        $this->loadStaffFromDB();
    }
}
