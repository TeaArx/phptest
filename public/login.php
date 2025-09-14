<?php
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/auth.php';
ensure_session();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $login = $_POST['login'] ?? '';
  $pass  = $_POST['password'] ?? '';
  if ($login === $_ENV['ADMIN_LOGIN'] && $pass === $_ENV['ADMIN_PASSWORD']) {
    $_SESSION['authed'] = true;
    header('Location: /admin.php');
    exit;
  } else {
    $error = 'Неверные логин или пароль';
  }
}
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
      min-width: 280px;
    }

    .form-field {
      display: block;
      margin: 0.5rem auto;
      padding: 8px 12px;
      width: 100%;
      max-width: 240px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font: inherit;
    }

    .nav-btn {
      display: inline-block;
      padding: 8px 16px;
      margin-top: 1rem;
      background: #007bff;
      color: #fff;
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

    .error {
      color: red;
      margin-bottom: 1rem;
    }
  </style>
</head>

<body>
  <div class="card">
    <h2>Вход</h2>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
      <input class="form-field" name="login" placeholder="Логин">
      <input class="form-field" name="password" type="password" placeholder="Пароль">
      <button type="submit" class="nav-btn">Войти</button>
    </form>
  </div>
</body>

</html>