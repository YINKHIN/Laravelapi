<?php
// Health check endpoint for Render.com

header('Content-Type: application/json');

try {
    // Check if Laravel is installed
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        throw new Exception('Laravel not installed');
    }
    
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Check database connection
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $database = $_ENV['DB_DATABASE'] ?? '';
    $username = $_ENV['DB_USERNAME'] ?? '';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    if (empty($database) || empty($username)) {
        throw new Exception('Database configuration missing');
    }
    
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 5,
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Test query
    $stmt = $pdo->query("SELECT 1 as db_connection");
    $result = $stmt->fetch();
    
    if (!$result) {
        throw new Exception('Database query failed');
    }
    
    // All checks passed
    http_response_code(200);
    echo json_encode([
        'status' => 'healthy',
        'timestamp' => date('c'),
        'database' => 'connected',
        'message' => 'Application is running normally'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'unhealthy',
        'timestamp' => date('c'),
        'error' => $e->getMessage(),
        'message' => 'Application requires attention'
    ]);
}