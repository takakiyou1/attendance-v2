<?php
require __DIR__ . '/config/database.php';

echo "<h1>Shift Data Repair Tool</h1>";

// Get all users
$users = $pdo->query("SELECT id, name, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
echo "<h2>Available Users:</h2><pre>";
print_r($users);
echo "</pre>";

// Check for orphaned shifts
$orphaned = $pdo->query("
    SELECT s.*
    FROM shifts s
    LEFT JOIN users u ON s.user_id = u.id
    WHERE u.id IS NULL
")->fetchAll(PDO::FETCH_ASSOC);

if (count($orphaned) > 0) {
    echo "<h2 style='color: red;'>⚠️ Found " . count($orphaned) . " orphaned shifts!</h2>";
    echo "<pre>";
    print_r($orphaned);
    echo "</pre>";

    echo "<hr>";
    echo "<h2>Fix Options:</h2>";
    echo "<form method='POST'>";
    echo "<p>Choose what to do with orphaned shifts:</p>";

    if (count($users) > 0) {
        echo "<p>";
        echo "<input type='radio' name='action' value='assign' id='assign'> ";
        echo "<label for='assign'>Assign all orphaned shifts to user: ";
        echo "<select name='user_id'>";
        foreach ($users as $u) {
            echo "<option value='{$u['id']}'>{$u['name']} (ID: {$u['id']}, Role: {$u['role']})</option>";
        }
        echo "</select></label>";
        echo "</p>";
    }

    echo "<p>";
    echo "<input type='radio' name='action' value='delete' id='delete'> ";
    echo "<label for='delete'>Delete all orphaned shifts</label>";
    echo "</p>";

    echo "<p><button type='submit'>Execute Fix</button></p>";
    echo "</form>";
} else {
    echo "<p style='color: green;'>✅ No orphaned shifts found!</p>";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'assign' && !empty($_POST['user_id'])) {
        $userId = (int)$_POST['user_id'];

        // Verify user exists
        $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Update orphaned shifts
            $stmt = $pdo->prepare("
                UPDATE shifts s
                LEFT JOIN users u ON s.user_id = u.id
                SET s.user_id = ?
                WHERE u.id IS NULL
            ");
            $stmt->execute([$userId]);
            $affected = $stmt->rowCount();

            echo "<div style='background: lightgreen; padding: 20px; margin: 20px 0;'>";
            echo "<h3>✅ Success!</h3>";
            echo "<p>Assigned {$affected} orphaned shifts to user: {$user['name']} (ID: {$userId})</p>";
            echo "<p><a href='test_data.php'>View updated data</a> | <a href='fix_shift_data.php'>Run again</a></p>";
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ Error: User ID {$userId} not found!</p>";
        }

    } elseif ($action === 'delete') {
        // Delete orphaned shifts
        $stmt = $pdo->query("
            DELETE s FROM shifts s
            LEFT JOIN users u ON s.user_id = u.id
            WHERE u.id IS NULL
        ");
        $affected = $stmt->rowCount();

        echo "<div style='background: lightyellow; padding: 20px; margin: 20px 0;'>";
        echo "<h3>✅ Deleted!</h3>";
        echo "<p>Removed {$affected} orphaned shifts from the database.</p>";
        echo "<p><a href='test_data.php'>View updated data</a> | <a href='fix_shift_data.php'>Run again</a></p>";
        echo "</div>";
    }
}

echo "<hr>";
echo "<p><a href='test_data.php'>← Back to Data Inspector</a> | <a href='test_api.php'>Test APIs</a></p>";
?>
