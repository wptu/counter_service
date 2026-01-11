<?php
/**
 * Admin Logout
 */

session_start();

require_once __DIR__ . '/../auth/Auth.php';

Auth::logout();

header('Location: /admin/login.php');
exit;
