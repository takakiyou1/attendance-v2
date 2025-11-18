<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>シフトカレンダー（新） | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
  <style>
    /* タイムツリー風カレンダー */
    .calendar-container {
      max-width: 600px;
      margin: 0 auto;
    }

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

    .calendar-header h2 {
      margin: 0;
      font-size: var(--text-xl);
      color: var(--color-text-primary);
    }

    .calendar-nav {
      display: flex;
      gap: var(--space-sm);
    }

    .calendar-nav button {
      background: var(--color-bg-tertiary);
      border: none;
      border-radius: var(--radius-md);
      padding: var(--space-sm) var(--space-md);
      font-size: var(--text-lg);
      cursor: pointer;
      transition: all var(--transition-fast);
    }

    .calendar-nav button:hover {
      background: var(--color-primary);
      color: white;
    }

    /* 曜日ヘッダー */
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

    .weekday-header span:first-child {
      color: var(--color-error);
    }

    .weekday-header span:last-child {
      color: var(--color-primary);
    }

    /* カレンダーグリッド */
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

    .calendar-day:hover {
      background: var(--color-bg-secondary);
    }

    .calendar-day.other-month {
      background: var(--color-bg-tertiary);
      opacity: 0.5;
    }

    .calendar-day.today {
      background: var(--color-primary-light);
    }

    .calendar-day.selected {
      background: var(--color-primary);
    }

    .calendar-day.selected .day-number {
      color: white;
    }

    .day-number {
      font-size: var(--text-sm);
      font-weight: var(--font-semibold);
      color: var(--color-text-primary);
      margin-bottom: var(--space-xs);
    }

    .calendar-day:nth-child(7n+1) .day-number {
      color: var(--color-error);
    }

    .calendar-day:nth-child(7n) .day-number {
      color: var(--color-primary);
    }

    /* シフトドット表示 */
    .shift-dots {
      display: flex;
      flex-wrap: wrap;
      gap: 2px;
    }

    .shift-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: var(--color-primary);
    }

    .shift-count {
      font-size: var(--text-xs);
      color: var(--color-text-tertiary);
    }

    /* 選択日のシフト詳細 */
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
      color: var(--color-text-primary);
      display: flex;
      align-items: center;
      gap: var(--space-sm);
    }

    .shift-list {
      display: flex;
      flex-direction: column;
      gap: var(--space-sm);
    }

    .shift-item {
      display: flex;
      align-items: center;
      gap: var(--space-md);
      padding: var(--space-sm) var(--space-md);
      background: var(--color-bg-secondary);
      border-radius: var(--radius-md);
      border-left: 4px solid var(--color-primary);
    }

    .shift-item .time {
      font-weight: var(--font-semibold);
      color: var(--color-text-primary);
      min-width: 100px;
    }

    .shift-item .name {
      color: var(--color-text-secondary);
      flex: 1;
    }

    .shift-item .color-indicator {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      border: 1px solid var(--color-border-dark);
    }

    .no-shifts {
      text-align: center;
      color: var(--color-text-tertiary);
      padding: var(--space-lg);
    }

    /* 新規追加ボタン */
    .add-shift-btn {
      width: 100%;
      margin-top: var(--space-md);
      padding: var(--space-md);
      background: var(--color-primary);
      color: white;
      border: none;
      border-radius: var(--radius-md);
      font-size: var(--text-base);
      font-weight: var(--font-semibold);
      cursor: pointer;
      transition: all var(--transition-fast);
    }

    .add-shift-btn:hover {
      background: var(--color-primary-hover);
    }

    /* モバイル最適化 */
    @media (max-width: 480px) {
      .calendar-day {
        min-height: 60px;
        padding: 4px;
      }

      .day-number {
        font-size: var(--text-xs);
      }

      .shift-dot {
        width: 6px;
        height: 6px;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <div class="page-header">
    <div class="container">
      <div class="page-header-content">
        <h1 class="page-title">
          📅 シフトカレンダー
        </h1>
        <div class="page-actions">
          <a href="<?= url('menu') ?>" class="btn btn-secondary">
            ← メニューに戻る
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container">
    <div class="calendar-container">
      <!-- カレンダーヘッダー -->
      <div class="calendar-header">
        <div class="calendar-nav">
          <button id="prevMonth">◀</button>
        </div>
        <h2 id="currentMonth">2024年1月</h2>
        <div class="calendar-nav">
          <button id="nextMonth">▶</button>
        </div>
      </div>

      <!-- 曜日ヘッダー -->
      <div class="weekday-header">
        <span>日</span>
        <span>月</span>
        <span>火</span>
        <span>水</span>
        <span>木</span>
        <span>金</span>
        <span>土</span>
      </div>

      <!-- カレンダーグリッド -->
      <div class="calendar-grid" id="calendarGrid">
        <!-- JavaScriptで生成 -->
      </div>

      <!-- 選択日の詳細 -->
      <div class="day-detail" id="dayDetail">
        <h3>
          <span id="selectedDate">日付を選択</span>
          <span class="badge badge-primary" id="shiftCount">0件</span>
        </h3>
        <div class="shift-list" id="shiftList">
          <div class="no-shifts">日付を選択してください</div>
        </div>
        <button class="add-shift-btn" id="addShiftBtn">＋ シフトを追加</button>
      </div>
    </div>
  </div>

  <script>
    const baseUrl = '<?= BASE_URL ?>';

    let currentDate = new Date();
    let selectedDate = null;
    let shiftsData = {};

    // 月の名前
    function formatMonth(date) {
      return `${date.getFullYear()}年${date.getMonth() + 1}月`;
    }

    // 日付をYYYY-MM-DD形式に
    function formatDate(date) {
      const y = date.getFullYear();
      const m = String(date.getMonth() + 1).padStart(2, '0');
      const d = String(date.getDate()).padStart(2, '0');
      return `${y}-${m}-${d}`;
    }

    // カレンダーを描画
    function renderCalendar() {
      const grid = document.getElementById('calendarGrid');
      const monthLabel = document.getElementById('currentMonth');

      monthLabel.textContent = formatMonth(currentDate);

      const year = currentDate.getFullYear();
      const month = currentDate.getMonth();

      // 月の最初と最後
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);

      // 前月の日数を取得
      const prevMonthDays = firstDay.getDay();
      const prevMonth = new Date(year, month, 0);

      let html = '';

      // 前月の日
      for (let i = prevMonthDays - 1; i >= 0; i--) {
        const day = prevMonth.getDate() - i;
        const date = new Date(year, month - 1, day);
        html += createDayCell(date, true);
      }

      // 当月の日
      for (let day = 1; day <= lastDay.getDate(); day++) {
        const date = new Date(year, month, day);
        html += createDayCell(date, false);
      }

      // 次月の日
      const remaining = 42 - (prevMonthDays + lastDay.getDate());
      for (let day = 1; day <= remaining; day++) {
        const date = new Date(year, month + 1, day);
        html += createDayCell(date, true);
      }

      grid.innerHTML = html;

      // クリックイベント
      grid.querySelectorAll('.calendar-day').forEach(cell => {
        cell.addEventListener('click', () => {
          const dateStr = cell.dataset.date;
          selectDate(dateStr);
        });
      });

      // シフトデータを取得
      fetchShifts();
    }

    // 日付セルを作成
    function createDayCell(date, isOtherMonth) {
      const dateStr = formatDate(date);
      const today = formatDate(new Date());
      const isToday = dateStr === today;
      const isSelected = selectedDate === dateStr;

      let classes = 'calendar-day';
      if (isOtherMonth) classes += ' other-month';
      if (isToday) classes += ' today';
      if (isSelected) classes += ' selected';

      return `
        <div class="${classes}" data-date="${dateStr}">
          <div class="day-number">${date.getDate()}</div>
          <div class="shift-dots" id="dots-${dateStr}"></div>
        </div>
      `;
    }

    // シフトデータを取得
    async function fetchShifts() {
      const year = currentDate.getFullYear();
      const month = currentDate.getMonth() + 1;
      const start = `${year}-${String(month).padStart(2, '0')}-01`;
      const end = `${year}-${String(month).padStart(2, '0')}-31`;

      try {
        const response = await fetch(`${baseUrl}/api/shifts/all?start=${start}&end=${end}`);
        const shifts = await response.json();

        // 日付ごとにグループ化
        shiftsData = {};
        shifts.forEach(shift => {
          const date = shift.start.split('T')[0];
          if (!shiftsData[date]) {
            shiftsData[date] = [];
          }
          shiftsData[date].push(shift);
        });

        // ドットを表示
        Object.keys(shiftsData).forEach(date => {
          const dotsEl = document.getElementById(`dots-${date}`);
          if (dotsEl) {
            const count = shiftsData[date].length;
            if (count <= 3) {
              dotsEl.innerHTML = shiftsData[date].map(s =>
                `<div class="shift-dot" style="background:${s.color || '#007bff'}"></div>`
              ).join('');
            } else {
              dotsEl.innerHTML = `<span class="shift-count">${count}件</span>`;
            }
          }
        });

        // 選択中の日があれば詳細を更新
        if (selectedDate) {
          showDayDetail(selectedDate);
        }
      } catch (error) {
        console.error('シフト取得エラー:', error);
      }
    }

    // 日付を選択
    function selectDate(dateStr) {
      // 前の選択を解除
      document.querySelectorAll('.calendar-day.selected').forEach(el => {
        el.classList.remove('selected');
      });

      // 新しい選択
      selectedDate = dateStr;
      const cell = document.querySelector(`[data-date="${dateStr}"]`);
      if (cell) {
        cell.classList.add('selected');
      }

      showDayDetail(dateStr);
    }

    // 日の詳細を表示
    function showDayDetail(dateStr) {
      const dateLabel = document.getElementById('selectedDate');
      const countLabel = document.getElementById('shiftCount');
      const listEl = document.getElementById('shiftList');

      const date = new Date(dateStr);
      const dayNames = ['日', '月', '火', '水', '木', '金', '土'];
      dateLabel.textContent = `${date.getMonth() + 1}月${date.getDate()}日（${dayNames[date.getDay()]}）`;

      const shifts = shiftsData[dateStr] || [];
      countLabel.textContent = `${shifts.length}件`;

      if (shifts.length === 0) {
        listEl.innerHTML = '<div class="no-shifts">シフトはありません</div>';
      } else {
        listEl.innerHTML = shifts.map(shift => {
          const start = shift.start.split('T')[1]?.substring(0, 5) || '';
          const end = shift.end.split('T')[1]?.substring(0, 5) || '';
          return `
            <div class="shift-item" style="border-left-color: ${shift.color || '#007bff'}">
              <span class="time">${start} - ${end}</span>
              <span class="name">${shift.title || '未設定'}</span>
              <span class="color-indicator" style="background: ${shift.color || '#007bff'}"></span>
            </div>
          `;
        }).join('');
      }
    }

    // イベントリスナー
    document.getElementById('prevMonth').addEventListener('click', () => {
      currentDate.setMonth(currentDate.getMonth() - 1);
      renderCalendar();
    });

    document.getElementById('nextMonth').addEventListener('click', () => {
      currentDate.setMonth(currentDate.getMonth() + 1);
      renderCalendar();
    });

    document.getElementById('addShiftBtn').addEventListener('click', () => {
      if (selectedDate) {
        // TODO: シフト追加モーダルを開く
        alert(`${selectedDate}にシフトを追加`);
      } else {
        alert('日付を選択してください');
      }
    });

    // 初期化
    renderCalendar();

    // 今日を選択
    selectDate(formatDate(new Date()));
  </script>
</body>
</html>
