<?php
/**
 * Test News Feature
 * Creates a sample news post for testing
 */

require __DIR__ . '/config/database.php';

try {
    // Get an admin user
    $admin = $pdo->query("SELECT id, name FROM users WHERE role='admin' LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo "❌ No admin user found. Please create an admin user first.\n";
        exit(1);
    }

    // Create a sample news post
    $stmt = $pdo->prepare("
        INSERT INTO news (title, content, user_id, display_date, is_pinned)
        VALUES (?, ?, ?, CURDATE(), 0)
    ");

    $stmt->execute([
        'システムメンテナンスのお知らせ',
        "明日の深夜2:00〜4:00の間、システムメンテナンスを実施します。\n\nこの時間帯はシステムにアクセスできなくなります。\nご不便をおかけしますが、ご了承ください。\n\n管理部",
        $admin['id']
    ]);

    $newsId = $pdo->lastInsertId();

    echo "✅ Sample news post created successfully!\n";
    echo "Post ID: {$newsId}\n";
    echo "Author: {$admin['name']}\n";
    echo "\nYou can view it at:\n";
    echo "http://localhost:8888/attendance-v2/public/index.php/news\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
