<?php
session_start();
require 'db.php';
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>全体シフトカレンダー（管理者）</title>

  <!-- FullCalendar v5.11.3 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>

  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body { font-family: sans-serif; padding: 20px; background: #f9fafb; }
    #calendar { max-width: 1000px; margin: 0 auto; background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    h1 { text-align: center; margin-bottom: 30px; }
    a { text-decoration: none; color: #007bff; }
    a:hover { text-decoration: underline; }
    /* モーダル */
    #shiftModal {
      display: none;
      position: fixed;
      top: 10%;
      left: 50%;
      transform: translateX(-50%);
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.3);
      z-index: 9999;
      width: 480px;
      max-width: 92%;
    }
    #shiftModal h3 { margin-top: 0; }
    #shiftModal ul { list-style: none; padding-left: 0; margin: 0; }
    #shiftModal li { padding: 6px 0; border-bottom: 1px solid #eee; }
    #shiftModal button { margin-top: 12px; padding: 6px 12px; }
  </style>
</head>
<body>
  <h1>📅 全体シフトカレンダー（管理者専用）</h1>

  <div id="calendar"></div>

  <p style="text-align:center; margin-top:30px;">
    <a href="menu.php">← メニューに戻る</a>
  </p>

  <!-- ▼ 出勤者一覧モーダル -->
  <div id="shiftModal">
    <div id="shiftModalBody"></div>
    <button onclick="document.getElementById('shiftModal').style.display='none'">閉じる</button>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarEl = document.getElementById('calendar');
      const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'ja',
        initialView: 'dayGridMonth',
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        slotMinTime: "09:00:00",
        slotMaxTime: "29:00:00",
        eventColor: '#3788d8',

        // 初期読み込みイベントURL（あなたの既存APIに合わせる）
        events: 'api/get_all_shifts_simple.php',

        // ✅ ビュー切替ごとにイベントソースを変更（案2）
        datesSet: function(viewInfo) {
          const viewType = viewInfo.view.type;
          const url = viewType === 'dayGridMonth'
            ? 'api/get_all_shifts_simple.php'
            : 'api/get_all_shifts_full.php';

          calendar.removeAllEventSources();
          calendar.addEventSource({
            url: url,
            failure: function() {
              alert("シフトデータの取得に失敗しました。");
            }
          });
        },

        // ✅ イベントクリック → 編集ページへ
        eventClick: function(info) {
          const shiftId = info.event.id;
          if (shiftId && confirm("このシフトを編集しますか？")) {
            window.location.href = `shift_edit.php?id=${shiftId}`;
          }
        },

        // ✅ 日付クリック → 出勤者一覧（堅牢版）をモーダル表示
        dateClick: function(info) {
          const date = info.dateStr;

          fetch('api/get_shifts_by_date.php?date=' + date)
            .then(response => response.json())
            .then(data => {
              const modal = document.getElementById('shiftModal');
              const body  = document.getElementById('shiftModalBody');

              if (!data || data.length === 0) {
                body.innerHTML = `<h3>${date}</h3><p>この日のシフトはありません。</p>`;
                modal.style.display = 'block';
                return;
              }

              // "HH:MM:SS" と "YYYY-MM-DD HH:MM:SS" のどちらでも時刻を抜き出す
              const pickHHMM = (s) => {
                if (typeof s !== 'string') return '';
                const m = s.match(/(\d{2}):(\d{2})/);
                return m ? `${m[1]}:${m[2]}` : '';
              };

              // 分に変換（ソート用）
              const toMin = (hhmm) => {
                const [h, m] = hhmm.split(':').map(Number);
                return (isNaN(h) || isNaN(m)) ? NaN : (h * 60 + m);
              };

              // ✅ ユーザーごとにまとめ、重複時間は除外、翌日跨ぎは（＋1）
              const grouped = {};
              data.forEach(shift => {
                const start = pickHHMM(shift.shift_start);
                const end   = pickHHMM(shift.shift_end);

                const startMin = toMin(start);
                let   endMin   = toMin(end);

                // 抽出失敗はスキップ（「：〜」を防ぐ）
                if (!start || !end || isNaN(startMin) || isNaN(endMin)) return;

                const crosses = endMin < startMin;  // 翌日またぎ？
                if (crosses) endMin += 24 * 60;

                const label = crosses ? `${start}〜${end}（＋1）` : `${start}〜${end}`;
                const uid   = shift.user_id;

                if (!grouped[uid]) {
                  grouped[uid] = { name: shift.user_name, slots: [] };
                }
                // 完全重複は追加しない
                if (!grouped[uid].slots.some(s => s.label === label)) {
                  grouped[uid].slots.push({ label, startMin });
                }
              });

              // ✅ 開始時刻でソートしてHTML出力
              let html = `<h3>${date} の出勤者一覧</h3><ul>`;
              Object.values(grouped).forEach(user => {
                user.slots.sort((a, b) => a.startMin - b.startMin);
                const times = user.slots.map(s => s.label).join('、');
                html += `<li><strong>${user.name}</strong>：${times}</li>`;
              });
              html += '</ul>';

              // ✅ 出勤者数（ユニーク人数）
              html += `<p style="text-align:right; color:#666;">出勤者数：${Object.keys(grouped).length}名</p>`;

              body.innerHTML = html;
              modal.style.display = 'block';
            })
            .catch(err => {
              alert('データ取得中にエラーが発生しました');
              console.error(err);
            });
        }
      });

      calendar.render();
    });
  </script>
</body>
</html>
