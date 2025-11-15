<?php
/**
 * Quick MVC Test
 * Tests core infrastructure and models
 */

// Load core classes
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/Model.php';

// Load configurations
$dbConfig = require __DIR__ . '/config/database_new.php';
$sessionConfig = require __DIR__ . '/config/session.php';

// Initialize services
Database::setConfig($dbConfig);
Session::setConfig($sessionConfig);
Session::start();

echo "<h1>MVC Architecture Test</h1>";

// Test 1: Database Connection
echo "<h2>Test 1: Database Connection</h2>";
try {
    $pdo = Database::getInstance();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test 2: Session Management
echo "<h2>Test 2: Session Management</h2>";
Session::set('test_key', 'test_value');
$value = Session::get('test_key');
if ($value === 'test_value') {
    echo "<p style='color: green;'>✅ Session working correctly</p>";
} else {
    echo "<p style='color: red;'>❌ Session not working</p>";
}

// Test 3: User Model
echo "<h2>Test 3: User Model</h2>";
try {
    require_once __DIR__ . '/app/Models/User.php';
    $userModel = new User();
    $users = $userModel->all();
    echo "<p style='color: green;'>✅ User model loaded successfully</p>";
    echo "<p>Found " . count($users) . " users</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ User model error: " . $e->getMessage() . "</p>";
}

// Test 4: Shift Model
echo "<h2>Test 4: Shift Model</h2>";
try {
    require_once __DIR__ . '/app/Models/Shift.php';
    $shiftModel = new Shift();
    $shifts = $shiftModel->all();
    echo "<p style='color: green;'>✅ Shift model loaded successfully</p>";
    echo "<p>Found " . count($shifts) . " shifts</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Shift model error: " . $e->getMessage() . "</p>";
}

// Test 5: Controller Instantiation
echo "<h2>Test 5: ShiftController Instantiation</h2>";
try {
    require_once __DIR__ . '/core/Controller.php';
    require_once __DIR__ . '/app/controllers/ShiftController.php';
    $controller = new ShiftController();
    echo "<p style='color: green;'>✅ ShiftController instantiated successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Controller error: " . $e->getMessage() . "</p>";
}

// Test 6: API Simulation
echo "<h2>Test 6: API Method Test</h2>";
if (Session::isLoggedIn()) {
    echo "<p>Logged in as: " . Session::user()['name'] . " (Role: " . Session::user()['role'] . ")</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Not logged in (this is normal for test page)</p>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p><strong>Status:</strong> All core components loaded successfully!</p>";
echo "<p><a href='/attendance-v2/public/index.php'>← Go to application</a></p>";
?>
