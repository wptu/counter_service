<?php
/**
 * Migration Script: Import staff from JSON to Database
 * Run this once to migrate existing data
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/StaffDB.php';

// Read JSON file
$jsonFile = __DIR__ . '/data/names.json';
if (!file_exists($jsonFile)) {
    die("Error: names.json not found\n");
}

$json = file_get_contents($jsonFile);
$data = json_decode($json, true);

if (!$data) {
    die("Error: Invalid JSON format\n");
}

$staffDB = new StaffDB();
$imported = 0;
$skipped = 0;

echo "🔄 Importing staff from JSON to database...\n\n";

// Import Group A
echo "Group A:\n";
foreach ($data['groupA'] as $staff) {
    $code = $staff['code'];
    $name = $staff['name'];

    if ($staffDB->codeExists($code)) {
        echo "  ⏭️  Skipped: $code (already exists)\n";
        $skipped++;
    } else {
        try {
            $staffDB->create($code, $name, 'A');
            echo "  ✅ Imported: $code - $name\n";
            $imported++;
        } catch (Exception $e) {
            echo "  ❌ Error: $code - " . $e->getMessage() . "\n";
        }
    }
}

echo "\nGroup B:\n";
// Import Group B
foreach ($data['groupB'] as $staff) {
    $code = $staff['code'];
    $name = $staff['name'];

    if ($staffDB->codeExists($code)) {
        echo "  ⏭️  Skipped: $code (already exists)\n";
        $skipped++;
    } else {
        try {
            $staffDB->create($code, $name, 'B');
            echo "  ✅ Imported: $code - $name\n";
            $imported++;
        } catch (Exception $e) {
            echo "  ❌ Error: $code - " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✨ Migration complete!\n";
echo "   Imported: $imported\n";
echo "   Skipped: $skipped\n";
