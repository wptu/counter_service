<?php
/**
 * Migration Script: Add RS Morning/Afternoon columns
 */

require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "🔄 Migrating Schedule Schema to support RS Split Shifts...\n\n";

try {
    // 1. Add rs_morning_id
    echo "Adding column 'rs_morning_id'...\n";
    try {
        $db->exec("ALTER TABLE schedules ADD COLUMN rs_morning_id INT NULL AFTER rs_id");
        $db->exec("ALTER TABLE schedules ADD CONSTRAINT fk_rs_morning FOREIGN KEY (rs_morning_id) REFERENCES staff(id)");
        echo "  ✅ Success\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate column name") !== false) {
            echo "  ⏭️  Already exists\n";
        } else {
            throw $e;
        }
    }

    // 2. Add rs_afternoon_id
    echo "Adding column 'rs_afternoon_id'...\n";
    try {
        $db->exec("ALTER TABLE schedules ADD COLUMN rs_afternoon_id INT NULL AFTER rs_morning_id");
        $db->exec("ALTER TABLE schedules ADD CONSTRAINT fk_rs_afternoon FOREIGN KEY (rs_afternoon_id) REFERENCES staff(id)");
        echo "  ✅ Success\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "Duplicate column name") !== false) {
            echo "  ⏭️  Already exists\n";
        } else {
            throw $e;
        }
    }

    echo "\n✨ Migration complete!\n";

} catch (Exception $e) {
    echo "\n❌ Migration Failed: " . $e->getMessage() . "\n";
    exit(1);
}
