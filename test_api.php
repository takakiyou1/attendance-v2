<?php
session_name('attendance_session');
session_set_cookie_params([
    'path' => '/attendance-v2',
    'domain' => 'localhost',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'None'
]);
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>API Test</title>
</head>
<body>
    <h1>API Test Page</h1>

    <h2>Session Info:</h2>
    <pre><?php print_r($_SESSION); ?></pre>

    <h2>Test API Calls:</h2>
    <button onclick="testAllShifts()">Test get_all_shifts.php</button>
    <button onclick="testMyShifts()">Test get_my_shifts.php</button>

    <h3>API Response:</h3>
    <pre id="output"></pre>

    <script>
    async function testAllShifts() {
        const response = await fetch('/attendance-v2/api/get_all_shifts.php', {
            credentials: 'include'
        });
        const data = await response.json();
        document.getElementById('output').textContent = JSON.stringify(data, null, 2);
        console.log('get_all_shifts.php response:', data);
    }

    async function testMyShifts() {
        const response = await fetch('/attendance-v2/api/get_my_shifts.php', {
            credentials: 'include'
        });
        const data = await response.json();
        document.getElementById('output').textContent = JSON.stringify(data, null, 2);
        console.log('get_my_shifts.php response:', data);
    }
    </script>
</body>
</html>
