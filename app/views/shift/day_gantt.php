<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($date) ?> のシフト</title>
  <link rel="stylesheet" href="https://unpkg.com/frappe-gantt/dist/frappe-gantt.css">
  <script src="https://unpkg.com/frappe-gantt/dist/frappe-gantt.umd.js"></script>
  <style>
    body { font-family: Meiryo, sans-serif; margin: 40px; }
    #gantt { border: 1px solid #ccc; border-radius: 8px; padding: 20px; }
    h1 { text-align: center; }
  </style>
</head>
<body>
  <h1>📊 <?= htmlspecialchars($date) ?> のシフト</h1>
  <div id="gantt"></div>
  <p style="text-align:center;"><a href="/attendance-v2/public/index.php/shift/calendar">← カレンダーに戻る</a></p>

  <script>
    fetch('/attendance-v2/api/get_shifts_by_date.php?date=<?= $date ?>')
      .then(res => res.json())
      .then(data => {
        const tasks = data.map(s => ({
          id: s.id,
          name: s.name + '（' + s.shift_start.substring(0,5) + '〜' + s.shift_end.substring(0,5) + '）',
          start: s.date + ' ' + s.shift_start,
          end: s.date + ' ' + s.shift_end,
          progress: 100
        }));

        new Gantt("#gantt", tasks, {
          view_mode: 'Hour',
          language: 'ja',
          custom_popup_html: function(task) {
            return `<b>${task.name}</b>`;
          }
        });
      });
  </script>
</body>
</html>
