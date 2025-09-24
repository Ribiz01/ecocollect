<?php
// test_db.php - Database connection test
echo "<!DOCTYPE html><html><head><title>Database Test</title><style>body{font-family:Arial,sans-serif;margin:40px}</style></head><body>";
echo "<h1>EcoCollect Database Test</h1>";

include_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo "<p style='color: green;'>✅ Database connected successfully!</p>";
    
    // Test tables
    $tables = ['users', 'user_profiles', 'driver_profiles', 'pickup_schedules', 'service_areas'];
    foreach ($tables as $table) {
        try {
            $result = $conn->query("SELECT 1 FROM $table LIMIT 1");
            echo "<p style='color: green;'>✅ Table '$table' exists</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Table '$table' missing</p>";
        }
    }
    
    // Test data
    try {
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>Total users: " . $result['count'] . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error counting users: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Database connection failed</p>";
}

echo "</body></html>";
?>