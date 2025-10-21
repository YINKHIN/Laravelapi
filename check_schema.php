<?php

require_once 'vendor/autoload.php';

try {
    $db = new PDO('sqlite:./database/database.sqlite');
    $stmt = $db->query('PRAGMA table_info(users)');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Users table structure:\n";
    echo "=====================\n";
    foreach ($columns as $column) {
        echo "Column: {$column['name']}\n";
        echo "Type: {$column['type']}\n";
        echo "Not Null: " . ($column['notnull'] ? 'YES' : 'NO') . "\n";
        echo "Default: " . ($column['dflt_value'] ?? 'NULL') . "\n";
        echo "Primary Key: " . ($column['pk'] ? 'YES' : 'NO') . "\n";
        echo "---------------------\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}