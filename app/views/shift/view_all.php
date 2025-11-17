<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>全体シフト | Re:time</title>
  <link rel="stylesheet" href="/attendance-v2/public/index.php/css/modern-design.css">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <style>
    .fc {
      background: var(--color-bg-primary);
      border-radius: var(--radius-lg);
      padding: var(--space-md);
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--color-border-light);
    }

    .fc .fc-toolbar-title {
      font-size: var(--text-2xl);
      font-weight: var(--font-semibold);
      color: var(--color-text-primary);
    }

    .fc .fc-button {
      background: var(--color-primary);
      border: none;
      border-radius: var(--radius-md);
      padding: var(--space-sm) var(--space-md);
      font-weight: var(--font-medium);
      transition: all var(--transition-fast);
    }

    .fc .fc-button:hover {
      background: var(--color-primary-hover);
    }

    .fc .fc-button-active {
      background: var(--color-primary-hover) !important;
    }

    .fc-theme-standard td,
    .fc-theme-standard th {
      border-color: var(--color-border-light);
    }

    .fc-col-header-cell {
      background: var(--color-bg-tertiary);
      padding: var(--space-sm);
      font-weight: var(--font-semibold);
      color: var(--color-text-secondary);
    }

    .fc-daygrid-day-number {
      color: var(--color-text-primary);
      padding: var(--space-xs);
      font-weight: var(--font-medium);
    }

    .fc-event {
      border-radius: var(--radius-sm);
      padding: 2px 4px;
      border: none;
      font-size: var(--text-sm);
    }
  </style>
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">
          📋 全体シフト
        </h1>
        <div class="page-actions">
          <a href="/attendance-v2/public/index.php/menu" class="btn btn-secondary">
            ← メニューに戻る
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <div id="calendar"></div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
      locale: 'ja',
      initialView: 'dayGridMonth',
      editable: false,
      selectable: false,
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek'
      },

      // ✅ 営業時間設定（09:00〜翌5:00 = 29:00）
      slotMinTime: '09:00:00',
      slotMaxTime: '29:00:00',
      nextDayThreshold: '09:00:00', // ✅ 深夜シフトを翌日扱いしない（9時前は当日扱い）

      // ✅ New MVC API endpoint
      events: '/attendance-v2/public/index.php/api/shifts/all',

      // ✅ 日付クリック時に一覧へ
      dateClick: function(info) {
        const returnUrl = encodeURIComponent('/shift/view_all');
        window.location.href = '/shift/my_day?date=' + info.dateStr + '&return=' + returnUrl;
      }
    });

    calendar.render();
  });
  </script>
</body>
</html>
