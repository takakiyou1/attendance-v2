<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>自分のシフト | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
  <style>
    .calendar-container { max-width: 600px; margin: 0 auto; }

    .calendar-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: var(--space-md);
      background: var(--color-bg-primary);
      border-radius: var(--radius-lg);
      margin-bottom: var(--space-md);
      box-shadow: var(--shadow-sm);
    }

    .calendar-header h2 { margin: 0; font-size: var(--text-xl); color: var(--color-text-primary); }
    .calendar-nav { display: flex; gap: var(--space-sm); }

    .calendar-nav button {
      background: var(--color-bg-tertiary);
      border: none;
      border-radius: var(--radius-md);
      padding: var(--space-sm) var(--space-md);
      font-size: var(--text-lg);
      cursor: pointer;
      transition: all var(--transition-fast);
    }

    .calendar-nav button:hover { background: var(--color-primary); color: white; }

    .weekday-header {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      background: var(--color-bg-tertiary);
      border-radius: var(--radius-md);
      margin-bottom: var(--space-sm);
      padding: var(--space-sm) 0;
    }

    .weekday-header span {
      text-align: center;
      font-size: var(--text-sm);
      font-weight: var(--font-semibold);
      color: var(--color-text-secondary);
    }

    .weekday-header span:first-child { color: var(--color-error); }
    .weekday-header span:last-child { color: var(--color-primary); }

    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 2px;
      background: var(--color-border-light);
      border-radius: var(--radius-lg);
      overflow: hidden;
    }

    .calendar-day {
      background: var(--color-bg-primary);
      min-height: 80px;
      padding: var(--space-xs);
      cursor: pointer;
      transition: all var(--transition-fast);
    }

    .calendar-day:hover { background: var(--color-bg-secondary); }
    .calendar-day.other-month { background: var(--color-bg-tertiary); opacity: 0.5; }
    .calendar-day.today { background: var(--color-primary-light); }
    .calendar-day.selected { background: var(--color-primary); }
    .calendar-day.selected .day-number { color: white !important; }

    .day-number {
      font-size: var(--text-sm);
      font-weight: var(--font-semibold);
      color: var(--color-text-primary);
      margin-bottom: var(--space-xs);
    }

    .calendar-day:nth-child(7n+1) .day-number { color: var(--color-error); }
    .calendar-day:nth-child(7n) .day-number { color: var(--color-primary); }

    .shift-dots { display: flex; flex-wrap: wrap; gap: 2px; }
    .shift-dot { width: 8px; height: 8px; border-radius: 50%; }
    .shift-count { font-size: var(--text-xs); color: var(--color-text-tertiary); }

    .day-detail {
      margin-top: var(--space-lg);
      background: var(--color-bg-primary);
      border-radius: var(--radius-lg);
      padding: var(--space-lg);
      box-shadow: var(--shadow-sm);
    }

    .day-detail h3 {
      margin: 0 0 var(--space-md) 0;
      font-size: var(--text-lg);
      display: flex;
      align-items: center;
      gap: var(--space-sm);
    }

    .shift-list { display: flex; flex-direction: column; gap: var(--space-sm); }

    .shift-item {
      display: flex;
      align-items: center;
      gap: var(--space-md);
      padding: var(--space-sm) var(--space-md);
      background: var(--color-bg-secondary);
      border-radius: var(--radius-md);
      border-left: 4px solid var(--color-primary);
    }

    .shift-item .time { font-weight: var(--font-semibold); flex: 1; }
    .shift-item .color-indicator { width: 12px; height: 12px; border-radius: 50%; border: 1px solid var(--color-border-dark); }
    .no-shifts { text-align: center; color: var(--color-text-tertiary); padding: var(--space-lg); }

    @media (max-width: 480px) {
      .calendar-day { min-height: 60px; padding: 4px; }
      .day-number { font-size: var(--text-xs); }
      .shift-dot { width: 6px; height: 6px; }
    }
  </style>
</head>
<body>
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">📅 <?= htmlspecialchars($_SESSION['user']['name']) ?> さんのシフト</h1>
        <div class="page-actions">
          <a href="<?= url('menu') ?>" class="btn btn-secondary">← メニューに戻る</a>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="calendar-container">
      <div class="calendar-header">
        <div class="calendar-nav"><button id="prevMonth">◀</button></div>
        <h2 id="currentMonth"></h2>
        <div class="calendar-nav"><button id="nextMonth">▶</button></div>
      </div>

      <div class="weekday-header">
        <span>日</span><span>月</span><span>火</span><span>水</span><span>木</span><span>金</span><span>土</span>
      </div>

      <div class="calendar-grid" id="calendarGrid"></div>

      <div class="day-detail">
        <h3>
          <span id="selectedDate">日付を選択</span>
          <span class="badge badge-primary" id="shiftCount">0件</span>
        </h3>
        <div class="shift-list" id="shiftList">
          <div class="no-shifts">日付を選択してください</div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const baseUrl = '<?= BASE_URL ?>';
    let currentDate = new Date();
    let selectedDate = null;
    let shiftsData = {};

    function formatMonth(date) { return `${date.getFullYear()}年${date.getMonth() + 1}月`; }
    function formatDate(date) {
      return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
    }

    function renderCalendar() {
      const grid = document.getElementById('calendarGrid');
      document.getElementById('currentMonth').textContent = formatMonth(currentDate);

      const year = currentDate.getFullYear(), month = currentDate.getMonth();
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const prevMonthDays = firstDay.getDay();
      const prevMonth = new Date(year, month, 0);

      let html = '';
      for (let i = prevMonthDays - 1; i >= 0; i--) html += createDayCell(new Date(year, month - 1, prevMonth.getDate() - i), true);
      for (let day = 1; day <= lastDay.getDate(); day++) html += createDayCell(new Date(year, month, day), false);
      const remaining = 42 - (prevMonthDays + lastDay.getDate());
      for (let day = 1; day <= remaining; day++) html += createDayCell(new Date(year, month + 1, day), true);

      grid.innerHTML = html;
      grid.querySelectorAll('.calendar-day').forEach(cell => cell.addEventListener('click', () => selectDate(cell.dataset.date)));
      fetchShifts();
    }

    function createDayCell(date, isOtherMonth) {
      const dateStr = formatDate(date);
      let classes = 'calendar-day';
      if (isOtherMonth) classes += ' other-month';
      if (dateStr === formatDate(new Date())) classes += ' today';
      if (selectedDate === dateStr) classes += ' selected';
      return `<div class="${classes}" data-date="${dateStr}"><div class="day-number">${date.getDate()}</div><div class="shift-dots" id="dots-${dateStr}"></div></div>`;
    }

    async function fetchShifts() {
      try {
        const response = await fetch(`${baseUrl}/api/shifts/my`);
        const shifts = await response.json();
        shiftsData = {};
        shifts.forEach(shift => {
          const date = shift.start.split('T')[0];
          if (!shiftsData[date]) shiftsData[date] = [];
          shiftsData[date].push(shift);
        });

        Object.keys(shiftsData).forEach(date => {
          const dotsEl = document.getElementById(`dots-${date}`);
          if (dotsEl) {
            const count = shiftsData[date].length;
            dotsEl.innerHTML = count <= 3
              ? shiftsData[date].map(s => `<div class="shift-dot" style="background:${s.color || '#3788d8'}"></div>`).join('')
              : `<span class="shift-count">${count}件</span>`;
          }
        });
        if (selectedDate) showDayDetail(selectedDate);
      } catch (error) { console.error('シフト取得エラー:', error); }
    }

    function selectDate(dateStr) {
      document.querySelectorAll('.calendar-day.selected').forEach(el => el.classList.remove('selected'));
      selectedDate = dateStr;
      document.querySelector(`[data-date="${dateStr}"]`)?.classList.add('selected');
      showDayDetail(dateStr);
    }

    function showDayDetail(dateStr) {
      const date = new Date(dateStr);
      const dayNames = ['日', '月', '火', '水', '木', '金', '土'];
      document.getElementById('selectedDate').textContent = `${date.getMonth() + 1}月${date.getDate()}日（${dayNames[date.getDay()]}）`;

      const shifts = shiftsData[dateStr] || [];
      document.getElementById('shiftCount').textContent = `${shifts.length}件`;
      document.getElementById('shiftList').innerHTML = shifts.length === 0
        ? '<div class="no-shifts">シフトはありません</div>'
        : shifts.map(shift => {
            const start = shift.start.split('T')[1]?.substring(0, 5) || '';
            const end = shift.end.split('T')[1]?.substring(0, 5) || '';
            return `<div class="shift-item" style="border-left-color:${shift.color || '#3788d8'}"><span class="time">${start} - ${end}</span><span class="color-indicator" style="background:${shift.color || '#3788d8'}"></span></div>`;
          }).join('');
    }

    document.getElementById('prevMonth').addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); });
    document.getElementById('nextMonth').addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); });

    renderCalendar();
    selectDate(formatDate(new Date()));
  </script>
</body>
</html>
