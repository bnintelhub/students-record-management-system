<?php
// config/db.php
// Database Configuration using PDO for security and SQL Injection prevention

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'Awesh@005';
$db_name = 'srms_db';

try {
    // First attempt to connect directly to the database
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // If database doesn't exist, connect without dbname and create automatically
    try {
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Read and execute schema.sql
        $schemaFile = __DIR__ . '/../database/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
            
            // Reconnect to newly created database
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } else {
            die("Database connection failed: Database does not exist and schema.sql was not found.");
        }
    } catch (PDOException $ex) {
        die("<div style='font-family:sans-serif;padding:20px;background:#fee2e2;color:#991b1b;border-radius:8px;margin:20px;'>
            <h3>Database Connection Failed</h3>
            <p>Please make sure Apache and MySQL are running in your XAMPP Control Panel.</p>
            <p><b>Error Details:</b> " . htmlspecialchars($ex->getMessage()) . "</p>
        </div>");
    }
}

// Global helper functions
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function base_url($path = '') {
    return '/students%20record%20management%20system/' . ltrim($path, '/');
}
?>
