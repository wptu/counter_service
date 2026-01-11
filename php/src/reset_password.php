<?php
require_once __DIR__ . '/config/database.php';

echo "Checking admin user...\n";

try {
    $db = Database::getInstance()->getConnection();

    // Check if admin exists
    $stmt = $db->prepare("SELECT * FROM users WHERE username = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch();

    $password = 'minad!123!';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    if ($user) {
        echo "✅ User 'admin' found. ID: " . $user['id'] . "\n";
        echo "Current hash: " . substr($user['password'], 0, 20) . "...\n";

        // Update password
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        if ($stmt->execute([$hash])) {
            echo "✅ Password updated successfully.\n";
        } else {
            echo "❌ Failed to update password.\n";
        }
    } else {
        echo "❌ User 'admin' NOT found. Creating...\n";

        $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES ('admin', ?, 'admin')");
        if ($stmt->execute([$hash])) {
            echo "✅ User 'admin' created successfully.\n";
        } else {
            echo "❌ Failed to create user.\n";
        }
    }

    // Verify login logic
    echo "\nVerifying login logic...\n";
    $stmt = $db->prepare("SELECT password FROM users WHERE username = 'admin'");
    $stmt->execute();
    $storedHash = $stmt->fetchColumn();

    if (password_verify($password, $storedHash)) {
        echo "✅ Login verification PASSED.\n";
        echo "Username: admin\n";
        echo "Password: " . $password . "\n";
    } else {
        echo "❌ Login verification FAILED.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
