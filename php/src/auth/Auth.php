<?php
/**
 * Authentication Handler
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Settings.php';

class Auth
{

    /**
     * Attempt to login user
     */
    public static function login(string $username, string $password): bool
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $username;
            $_SESSION['is_admin'] = true;
            return true;
        }

        return false;
    }

    /**
     * Logout user
     */
    public static function logout(): void
    {
        session_unset();
        session_destroy();
    }

    /**
     * Check if user is logged in as admin
     */
    public static function isAdmin(): bool
    {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }

    /**
     * Require admin login or redirect to login page
     */
    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            header('Location: login.php');
            exit;
        }
    }

    /**
     * Get current admin username
     */
    public static function getAdminUsername(): ?string
    {
        return $_SESSION['admin_username'] ?? null;
    }

    /**
     * Attempt to login with public password
     */
    public static function loginPublic(string $password): bool
    {
        // Check against defined constant
        if (password_verify($password, PUBLIC_PASSWORD_HASH)) {
            $_SESSION['public_auth'] = true;
            return true;
        }
        return false;
    }

    /**
     * Check if user has public access (or is admin)
     */
    public static function isPublicAuthenticated(): bool
    {
        // Admin implies public access
        if (self::isAdmin()) {
            return true;
        }

        // Check global setting
        $enabled = Settings::get('public_login_enabled', '1');
        if ($enabled === '0') {
            return true; // Login disabled means public access for everyone
        }

        return isset($_SESSION['public_auth']) && $_SESSION['public_auth'] === true;
    }
}
