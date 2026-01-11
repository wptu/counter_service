<?php
/**
 * Application Constants
 */

define('YEAR', 2026);

// Schedule constraints
define('MAX_CONSECUTIVE_DAYS', 2);
define('MIN_GROUP_B_SHIFTS', 1);

// Public Access Password (Default: regtu2569)
// generated with password_hash('regtu2569', PASSWORD_DEFAULT)
define('PUBLIC_PASSWORD_HASH', '$2y$12$HsBh1e0wnHCNYk73mP62OueRoADs7jPbLWeK125afubX1RhGybvvq');

define('TIMEZONE', 'Asia/Bangkok');

// Thai day names
define('THAI_DAYS', [
    'อาทิตย์',
    'จันทร์',
    'อังคาร',
    'พุธ',
    'พฤหัสบดี',
    'ศุกร์',
    'เสาร์'
]);

// Thai month names
define('THAI_MONTHS', [
    'มกราคม',
    'กุมภาพันธ์',
    'มีนาคม',
    'เมษายน',
    'พฤษภาคม',
    'มิถุนายน',
    'กรกฎาคม',
    'สิงหาคม',
    'กันยายน',
    'ตุลาคม',
    'พฤศจิกายน',
    'ธันวาคม'
]);

// Thai month abbreviations
define('THAI_MONTHS_SHORT', [
    'ม.ค.',
    'ก.พ.',
    'มี.ค.',
    'เม.ย.',
    'พ.ค.',
    'มิ.ย.',
    'ก.ค.',
    'ส.ค.',
    'ก.ย.',
    'ต.ค.',
    'พ.ย.',
    'ธ.ค.'
]);

// Full week months (Jan=0, Feb=1, Jul=6, Aug=7)
define('FULL_WEEK_MONTHS', [0, 1, 6, 7]);

// Target RS ratio (B:A = 80:20)
define('RS_TARGET_B_RATIO', 0.8);
