<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>シフト管理カレンダー</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

  <style>
    body {
      font-family: Meiryo, sans-serif;
      margin: 40px;
      background-color: #f9f9f9;
      text-align: center;
    }
    #calendar { max-width: 900px; margin: 0 auto; }

    /* モーダル背景 */
    #modal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }

    /* モーダル本体 */
    #modal-content {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      width: 400px;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 0 20px rgba(0,0,0,0.3);
    }

    label { display: block; margin-top: 8px; }
    input, select { width: 100%; padding: 8px; margin-top: 4px; }

    button {
      margin-top: 10px;
      padding: 8px 14px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .save { background: #007bff; color: white; }
    .repeat { background: #28a745; color: white; }
    .close { background: #6c757d; color: white; }

    @media (max-width: 768px) {
      #modal-content {
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        font-size: 14px;
      }
      #calendar {
        width: 95%;
        margin: 0 auto;
      }
      button {
        font-size: 14px;
        padding: 6px 10px;
      }
      h1 { font-size: 20px; }
    }

    /* ✅ メニューに戻るボタン */
    .back-btn {
      display: inline-block;
      margin-top: 30px;
      background: #007bff;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      text-decoration: none;
      transition: 0.2s;
    }
    .back-btn:hover {
      background: #0056b3;
    }
  </style>
</head>

<body>
  <h1>📅 シフト管理カレンダー</h1>
  <div id="calendar"></div>

  <!-- ✅ メニューに戻るリンク（シンプル） -->
<p style="margin-top: 30px;">
  <a href="/attendance-v2/public/index.php/menu">← メニューに戻る</a>
</p>


  <!-- モーダル -->
  <div id="modal">
    <div id="modal-content">
      <h3 id="modal-title">シフト登録／編集</h3>
      <form id="shiftForm">
        <input type="hidden" name="id" id="shift-id">

        <label>スタッフ</label>
        <select name="user_id" id="user_id" required>
          <option value="">選択してください</option>
          <?php
          require __DIR__ . '/../../../config/database.php';
          $users = $pdo->query("SELECT id, name FROM users WHERE role='employee'")->fetchAll(PDO::FETCH_ASSOC);
          foreach ($users as $u) {
            echo "<option value='{$u['id']}'>{$u['name']}</option>";
          }
          ?>
        </select>

        <label>日付</label>
        <input type="date" name="date" id="date" required>

        <label>開始時間</label>
        <input type="time" name="shift_start" id="shift_start" required>

        <label>終了時間</label>
        <input type="time" name="shift_end" id="shift_end" required>

        <label>色</label>
        <select name="color" id="color">
          <option value="#000000">Black (#000000)</option>
          <option value="#ffffff">White (#ffffff)</option>
          <option value="#ff0000">Red (#ff0000)</option>
          <option value="#0000ff">Blue (#0000ff)</option>
          <option value="#008000">Green (#008000)</option>
          <option value="#ffff00">Yellow (#ffff00)</option>
        </select>

        <!-- 🔁 繰り返し設定エリア -->
        <div id="repeatArea" style="margin-top:10px; border-top:1px solid #ccc; padding-top:10px; display:none;">
          <h4>🔁 繰り返し設定</h4>

          <label>繰り返しタイプ</label>
          <select name="repeat_type" id="repeat_type">
            <option value="">なし</option>
            <option value="daily">毎日</option>
            <option value="weekly">毎週</option>
            <option value="monthly">毎月</option>
          </select>

          <div id="weekday-options" style="margin-top:5px;">
            <label>曜日指定：</label><br>
            <?php
            $days = ['月','火','水','木','金','土','日'];
            foreach ($days as $i => $d) {
              echo "<label><input type='checkbox' name='days[]' value='".($i+1)."'> {$d}</label> ";
            }
            ?>
          </div>

          <label>繰り返し終了日</label>
          <input type="date" name="repeat_end" id="repeat_end">
        </div>

        <!-- ボタン群 -->
        <div style="text-align:center;">
          <button type="submit" class="save">💾 保存</button>
          <button type="button" id="deleteSingleBtn" style="background:#ff6666;color:#fff;">🗑 このシフトを削除</button>
          <button type="button" id="repeatBtn" class="repeat">🔁 繰り返し設定</button>
          <button type="button" id="editGroupBtn" style="background:#ffc107;color:#000;">✏️ 繰り返し全体を変更</button>
          <button type="button" id="deleteGroupBtn" style="background:#dc3545;color:#fff;">🗑 繰り返し全体を削除</button>
          <button type="button" id="closeBtn" class="close">✖ 閉じる</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const modal = document.getElementById('modal');
    const form = document.getElementById('shiftForm');
    const repeatBtn = document.getElementById('repeatBtn');
    const closeBtn = document.getElementById('closeBtn');

    // ✅ モーダルを開く関数
    function openModal(data = {}) {
      document.getElementById('shift-id').value = data.id || '';
      document.getElementById('user_id').value = data.user_id || '';
      document.getElementById('date').value = data.date || '';
      document.getElementById('shift_start').value = data.shift_start || '';
      document.getElementById('shift_end').value = data.shift_end || '';
      document.getElementById('color').value = data.color || '#0000ff';

      // ✅ 繰り返しシフトならタイトルに表示
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
        slotMinTime: "09:00:00",   // ✅ 開始時間（営業開始）
        slotMaxTime: "29:00:00",   // ✅ 終了時間（翌5時 = 29時）
        nextDayThreshold: "09:00:00", // ✅ 深夜シフトを翌日扱いしない（9時前は当日扱い）
        events: '/attendance-v2/public/index.php/api/shifts/all',

        dateClick: function(info) {
          const target = info.jsEvent.target;
          if (target.classList.contains('fc-daygrid-day-number')) {
            window.location.href = '/attendance-v2/public/index.php/shift/day?date=' + info.dateStr;
            return;
          }
          openModal({ date: info.dateStr });
        },

        eventClick: function(info) {
          fetch('/attendance-v2/public/index.php/api/shifts/by-id?id=' + info.event.id)
            .then(res => res.json())
            .then(data => {
              openModal(data);
              const shiftIdField = document.getElementById('shift-id');
              shiftIdField.dataset.repeatId = data.repeat_id || '';
            });
        }
      });
      calendar.render();

      // 単発保存
      form.addEventListener('submit', e => {
        e.preventDefault();
        fetch('/attendance-v2/public/index.php/shift/save', {
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

      // 繰り返し設定
      repeatBtn.addEventListener('click', () => {
        const area = document.getElementById('repeatArea');
        if (area.style.display === 'none') {
          area.style.display = 'block';
        } else {
          fetch('/attendance-v2/public/index.php/shift/save_repeat', {
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

      // 繰り返し削除
      document.getElementById('deleteGroupBtn').addEventListener('click', () => {
        const shiftIdField = document.getElementById('shift-id');
        const repeat_id = shiftIdField.dataset.repeatId;
        if (!repeat_id) return alert('このシフトは繰り返し登録ではありません');
        if (!confirm('この繰り返し全体を削除しますか？')) return;

        fetch('/attendance-v2/public/index.php/api/shifts/delete-group', {
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

      // ✅ 繰り返し全体を変更
      document.getElementById('editGroupBtn').addEventListener('click', () => {
        const shiftIdField = document.getElementById('shift-id');
        const repeat_id = shiftIdField.dataset.repeatId;
        if (!repeat_id) return alert('このシフトは繰り返し登録ではありません');
        if (!confirm('この繰り返し全体を変更しますか？')) return;

        fetch('/attendance-v2/public/index.php/api/shifts/update-group', {
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

      // ✅ 単発シフト削除
      document.getElementById('deleteSingleBtn').addEventListener('click', () => {
        const id = document.getElementById('shift-id').value;
        if (!id) return alert('削除対象がありません');
        if (!confirm('このシフトを削除しますか？')) return;

        fetch('/attendance-v2/public/index.php/shift/delete', {
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

      // 閉じる
      closeBtn.addEventListener('click', closeModal);
      modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    });
  </script>
</body>
</html>
