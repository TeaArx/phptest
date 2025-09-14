<?php
require __DIR__ . '/../src/auth.php';
require_auth();
?>
<!doctype html>
<html>

<head>
  <style>
    body {
      font-family: sans-serif;
      margin: 0;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #f5f5f5;
    }

    .card {
      background: #fff;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      text-align: center;
      min-width: 320px;
    }

    .nav-btn {
      display: inline-block;
      padding: 6px 12px;
      margin: 0 4px;
      background: #007bff;
      color: #fff;
      text-decoration: none;
      border: none;
      border-radius: 4px;
      font: inherit;
      cursor: pointer;
    }

    .nav-btn:hover {
      background: #0056b3;
    }

    .nav-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    #status {
      margin-top: 1rem;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <div class="card">
    <h1>Сбор данных</h1>
    <p>
      <button id="runBtn" type="button" class="nav-btn">Запустить сбор</button>
      <a href="/export.php" class="nav-btn">Скачать Excel</a>
      <a href="/logout.php" class="nav-btn">Выход</a>
    </p>
    <div id="status"></div>
  </div>

  <script>
    const btn = document.getElementById('runBtn');
    const status = document.getElementById('status');

    btn.addEventListener('click', async () => {
      btn.disabled = true;
      status.textContent = 'Сбор запущен...';

      try {
        const resp = await fetch('/run.php', {
          method: 'POST',
          headers: {
            'Accept': 'application/json'
          }
        });

        if (!resp.ok) {
          let msg = `HTTP ${resp.status}`;
          if (resp.status === 401 || resp.status === 403) {
            msg = 'Нет доступа';
          } else if (resp.status >= 500) {
            msg = 'Внутренняя ошибка сервера';
          }
          throw new Error(msg);
        }

        const data = await resp.json();
        status.textContent = data.ok ?
          `Готово. Добавлено: ${data.inserted}` :
          `Ошибка: ${data.error}`;
      } catch (err) {
        // fetch может выбросить ошибку сети (например, сервер не запущен)
        const message = (err instanceof TypeError) ?
          'Сервер недоступен' :
          err.message;
        status.textContent = `Ошибка: ${message}`;
      } finally {
        btn.disabled = false;
      }
    });
  </script>

</body>

</html>