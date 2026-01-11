<?php
/**
 * Settings Model
 * Manage system configuration
 */

require_once __DIR__ . '/../config/database.php';

class Settings
{
    private static ?array $cache = null;

    /**
     * Get a setting value
     */
    public static function get(string $key, $default = null): ?string
    {
        // Load cache if empty
        if (self::$cache === null) {
            self::loadAll();
        }

        return self::$cache[$key] ?? $default;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, ?string $value): bool
    {
        $db = Database::getInstance()->getConnection();

        try {
            if ($value === null) {
                // Delete setting
                $stmt = $db->prepare("DELETE FROM settings WHERE setting_key = ?");
                $result = $stmt->execute([$key]);
                if ($result) {
                    unset(self::$cache[$key]);
                }
                return $result;
            } else {
                // Upsert setting
                $stmt = $db->prepare("
                    INSERT INTO settings (setting_key, setting_value) 
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                $result = $stmt->execute([$key, $value]);
                if ($result) {
                    self::$cache[$key] = $value;
                }
                return $result;
            }
        } catch (Exception $e) {
            error_log("Error setting $key: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Load all settings into cache
     */
    private static function loadAll(): void
    {
        $db = Database::getInstance()->getConnection();
        self::$cache = [];

        try {
            $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
            while ($row = $stmt->fetch()) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            error_log("Error loading settings: " . $e->getMessage());
            // Fail silently or just empty cache
        }
    }

    /**
     * Refresh cache from database
     */
    public static function refresh(): void
    {
        self::loadAll();
    }
}
