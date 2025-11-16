<?php
/**
 * Run News Table Migration
 * Execute this file once to create the news table
 */

require __DIR__ . '/../config/database.php';

try {
    // Read and execute SQL migration
    $sql = file_get_contents(__DIR__ . '/create_news_table.sql');

    // Remove comments and split by semicolon
    $statements = array_filter(
        array_map('trim', explode(';', preg_replace('/--.*$/m', '', $sql))),
        function($stmt) { return !empty($stmt); }
    );

    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }

    echo "✅ News table created successfully!\n";
    echo "You can now access the news feature at:\n";
    echo "- View news: /attendance-v2/public/index.php/news\n";
    echo "- Create news: /attendance-v2/public/index.php/news/create (admin/poster only)\n";

} catch (PDOException $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
