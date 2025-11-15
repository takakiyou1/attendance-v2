<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>全体シフト（閲覧専用）</title>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<style>
body { font-family: Meiryo, sans-serif; margin: 40px; text-align:center; background:#fafafa; }
#calendar { max-width: 900px; margin: 0 auto; }
</style>
</head>
<body>
  <h1>📅 全体シフト（閲覧専用）</h1>
  <div id="calendar"></div>
  <p><a href="/attendance-v2/public/index.php/menu">← メニューに戻る</a></p>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
      locale: 'ja',
      initialView: 'dayGridMonth',
      editable: false,
      selectable: false,

      // ✅ New MVC API endpoint
      events: '/attendance-v2/public/index.php/api/shifts/all',

      // ✅ 日付クリック時に一覧へ
      dateClick: function(info) {
        const returnUrl = encodeURIComponent('/attendance-v2/public/index.php/shift/view_all');
        window.location.href = '/attendance-v2/public/index.php/shift/my_day?date=' + info.dateStr + '&return=' + returnUrl;
      }
    });

    calendar.render();
  });
  </script>
</body>
</html>
