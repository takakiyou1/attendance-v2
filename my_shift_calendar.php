<?php
session_start();
require 'db.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>カレンダーでシフト確認</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/main.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/locales-all.min.js"></script>
</head>
<body>
  <h1>📅 あなたのシフト（カレンダー表示）</h1>

  <div id="calendar" style="max-width: 900px; margin: 20px auto;"></div>

  <p><a href="menu.php">← メニューに戻る</a></p>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarEl = document.getElementById('calendar');
      const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'ja',
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek'
        },
        events: 'api/get_my_shifts.php', // ← Ajaxで取得
        eventColor: '#3a87ad'
      });
      calendar.render();
    });
  </script>
</body>
</html>
