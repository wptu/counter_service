<?php
header('Content-Type: text/html; charset=utf-8');

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$db = getenv('DB_NAME');

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Document Delivery System</h1>";
echo "<p style='color: green;'>✅ Connected to database successfully!</p>";

// Basic query to verify access to existing data
$sql = "SELECT count(*) as count FROM users";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>Existing users count from 'shift_scheduler' database: <strong>" . $row['count'] . "</strong></p>";
} else {
    echo "<p style='color: red;'>Error querying database: " . $conn->error . "</p>";
}

$conn->close();
?>