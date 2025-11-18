<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>シフト管理カレンダー | Re:time</title>
  <link rel="stylesheet" href="<?= asset('css/modern-design.css') ?>">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
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

    /* Modal Styles */
    #modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    #modal-content {
      background: var(--color-bg-primary);
      padding: var(--space-xl);
      border-radius: var(--radius-lg);
      width: 90%;
      max-width: 450px;
      max-height: 85vh;
      overflow-y: auto;
      box-shadow: var(--shadow-xl);
    }

    #modal-content h3 {
      margin: 0 0 var(--space-lg) 0;
      color: var(--color-text-primary);
      font-size: var(--text-xl);
    }

    #repeatArea {
      margin-top: var(--space-md);
      border-top: 1px solid var(--color-border-light);
      padding-top: var(--space-md);
    }

    #repeatArea h4 {
      margin: 0 0 var(--space-sm) 0;
      color: var(--color-text-primary);
    }

    .modal-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: var(--space-sm);
      margin-top: var(--space-lg);
    }

    .modal-buttons button {
      flex: 1;
      min-width: 120px;
    }

    @media (max-width: 768px) {
      #modal-content {
        width: 95%;
        max-height: 90vh;
        padding: var(--space-lg);
      }
      .modal-buttons button {
        font-size: var(--text-sm);
        padding: var(--space-sm) var(--space-md);
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
          📅 シフト管理カレンダー
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
    <div id="calendar"></div>
  </div>

  <!-- Modal -->
  <div id="modal">
    <div id="modal-content">
      <h3 id="modal-title">シフト登録／編集</h3>
      <form id="shiftForm">
        <input type="hidden" name="id" id="shift-id">

        <div class="form-group">
          <label class="form-label">スタッフ</label>
          <select name="user_id" id="user_id" class="form-select" required>
            <option value="">選択してください</option>
            <?php
            require __DIR__ . '/../../../config/database.php';
            $users = $pdo->query("SELECT id, name FROM users WHERE role='employee'")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($users as $u) {
              echo "<option value='{$u['id']}'>{$u['name']}</option>";
            }
            ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">日付</label>
          <input type="date" name="date" id="date" class="form-input" required>
        </div>

        <div class="form-group">
          <label class="form-label">開始時間</label>
          <input type="time" name="shift_start" id="shift_start" class="form-input" required>
        </div>

        <div class="form-group">
          <label class="form-label">終了時間</label>
          <input type="time" name="shift_end" id="shift_end" class="form-input" required>
        </div>

        <div class="form-group">
          <label class="form-label">色</label>
          <select name="color" id="color" class="form-select">
            <option value="#000000">Black (#000000)</option>
            <option value="#ffffff">White (#ffffff)</option>
            <option value="#ff0000">Red (#ff0000)</option>
            <option value="#0000ff">Blue (#0000ff)</option>
            <option value="#008000">Green (#008000)</option>
            <option value="#ffff00">Yellow (#ffff00)</option>
          </select>
        </div>

        <!-- Repeat Settings -->
        <div id="repeatArea" style="display:none;">
          <h4>🔁 繰り返し設定</h4>

          <div class="form-group">
            <label class="form-label">繰り返しタイプ</label>
            <select name="repeat_type" id="repeat_type" class="form-select">
              <option value="">なし</option>
              <option value="daily">毎日</option>
              <option value="weekly">毎週</option>
              <option value="monthly">毎月</option>
            </select>
          </div>

          <div id="weekday-options" class="form-group">
            <label class="form-label">曜日指定：</label>
            <div style="display: flex; flex-wrap: wrap; gap: var(--space-sm);">
              <?php
              $days = ['月','火','水','木','金','土','日'];
              foreach ($days as $i => $d) {
                echo "<label style='display: flex; align-items: center; gap: var(--space-xs);'><input type='checkbox' name='days[]' value='".($i+1)."'> {$d}</label>";
              }
              ?>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">繰り返し終了日</label>
            <input type="date" name="repeat_end" id="repeat_end" class="form-input">
          </div>
        </div>

        <!-- Buttons -->
        <div class="modal-buttons">
          <button type="submit" class="btn btn-primary">💾 保存</button>
          <button type="button" id="deleteSingleBtn" class="btn btn-danger">🗑 削除</button>
          <button type="button" id="repeatBtn" class="btn btn-success">🔁 繰り返し</button>
          <button type="button" id="editGroupBtn" class="btn btn-warning">✏️ 全体変更</button>
          <button type="button" id="deleteGroupBtn" class="btn btn-danger">🗑 全体削除</button>
          <button type="button" id="closeBtn" class="btn btn-secondary">✖ 閉じる</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Base URL for API calls
    const baseUrl = '<?= BASE_URL ?>';

    const modal = document.getElementById('modal');
    const form = document.getElementById('shiftForm');
    const repeatBtn = document.getElementById('repeatBtn');
    const closeBtn = document.getElementById('closeBtn');

    // Open Modal
    function openModal(data = {}) {
      document.getElementById('shift-id').value = data.id || '';
      document.getElementById('user_id').value = data.user_id || '';
      document.getElementById('date').value = data.date || '';
      document.getElementById('shift_start').value = data.shift_start || '';
      document.getElementById('shift_end').value = data.shift_end || '';
      document.getElementById('color').value = data.color || '#0000ff';

      const modalTitle = document.getElementById('modal-title');
      if (data.repeat_id) {
        modalTitle.textContent = `シフト登録／編集　※繰り返し（グループID: ${data.repeat_id})`;
      } else {
        modalTitle.textContent = 'シフト登録／編集';
      }

      modal.style.display = 'flex';
    }

    function closeModal() {
      modal.style.display = 'none';
      form.reset();
      document.getElementById('repeatArea').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
      const calendarEl = document.getElementById('calendar');
      const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'ja',
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek'
        },
        slotMinTime: "09:00:00",
        slotMaxTime: "29:00:00",
        nextDayThreshold: "09:00:00",
        events: baseUrl + '/api/shifts/all',

        dateClick: function(info) {
          const target = info.jsEvent.target;
          if (target.classList.contains('fc-daygrid-day-number')) {
            window.location.href = baseUrl + '/shift/my_day?date=' + info.dateStr + '&return=calendar';
            return;
          }
          openModal({ date: info.dateStr });
        },

        eventClick: function(info) {
          fetch(baseUrl + '/api/shifts/by-id?id=' + info.event.id)
            .then(res => res.json())
            .then(data => {
              openModal(data);
              const shiftIdField = document.getElementById('shift-id');
              shiftIdField.dataset.repeatId = data.repeat_id || '';
            });
        }
      });
      calendar.render();

      // Save single shift
      form.addEventListener('submit', e => {
        e.preventDefault();
        fetch(baseUrl + '/shift/save', {
          method: 'POST',
          body: new FormData(form)
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'error') {
            alert(res.message);
            return;
          }
          alert(res.message || '保存しました');
          closeModal();
          calendar.refetchEvents();
        })
        .catch(err => {
          console.error(err);
          alert('エラーが発生しました');
        });
      });

      // Repeat settings
      repeatBtn.addEventListener('click', () => {
        const area = document.getElementById('repeatArea');
        if (area.style.display === 'none') {
          area.style.display = 'block';
        } else {
          fetch(baseUrl + '/shift/save_repeat', {
            method: 'POST',
            body: new FormData(form)
          })
          .then(res => res.json())
          .then(res => {
            alert(res.message);
            if (res.status === 'success') {
              closeModal();
              calendar.refetchEvents();
            }
          })
          .catch(err => {
            console.error(err);
            alert('エラーが発生しました');
          });
        }
      });

      // Delete repeat group
      document.getElementById('deleteGroupBtn').addEventListener('click', () => {
        const shiftIdField = document.getElementById('shift-id');
        const repeat_id = shiftIdField.dataset.repeatId;
        if (!repeat_id) return alert('このシフトは繰り返し登録ではありません');
        if (!confirm('この繰り返し全体を削除しますか？')) return;

        fetch(baseUrl + '/api/shifts/delete-group', {
          method: 'POST',
          body: new URLSearchParams({ repeat_id })
        })
        .then(res => res.json())
        .then(res => {
          alert(res.message);
          closeModal();
          calendar.refetchEvents();
        });
      });

      // Edit repeat group
      document.getElementById('editGroupBtn').addEventListener('click', () => {
        const shiftIdField = document.getElementById('shift-id');
        const repeat_id = shiftIdField.dataset.repeatId;
        if (!repeat_id) return alert('このシフトは繰り返し登録ではありません');
        if (!confirm('この繰り返し全体を変更しますか？')) return;

        fetch(baseUrl + '/api/shifts/update-group', {
          method: 'POST',
          body: new URLSearchParams({
            repeat_id,
            user_id: document.getElementById('user_id').value,
            shift_start: document.getElementById('shift_start').value,
            shift_end: document.getElementById('shift_end').value
          })
        })
        .then(res => res.json())
        .then(res => {
          alert(res.message);
          closeModal();
          calendar.refetchEvents();
        })
        .catch(err => console.error(err));
      });

      // Delete single shift
      document.getElementById('deleteSingleBtn').addEventListener('click', () => {
        const id = document.getElementById('shift-id').value;
        if (!id) return alert('削除対象がありません');
        if (!confirm('このシフトを削除しますか？')) return;

        fetch(baseUrl + '/shift/delete', {
          method: 'POST',
          body: new URLSearchParams({ id })
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') {
            alert(res.message || 'シフトを削除しました');
            closeModal();
            calendar.refetchEvents();
          } else {
            alert(res.message || '削除に失敗しました');
          }
        })
        .catch(err => {
          console.error(err);
          alert('エラーが発生しました');
        });
      });

      // Close modal
      closeBtn.addEventListener('click', closeModal);
      modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    });
  </script>
</body>
</html>
