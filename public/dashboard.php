<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/auth.php';
require_login();
$user = current_user();
require_once __DIR__ . '/../app/db.php';

// Simple stats: latest tests and score per test
$stmt = $pdo->query("SELECT t.id, e.full_name,
    SUM(CASE WHEN ta.is_correct THEN 1 ELSE 0 END) AS correct,
    COUNT(ta.*) AS total, t.started_at, t.finished_at
    FROM tests t
    JOIN employees e ON e.id = t.employee_id
    LEFT JOIN test_answers ta ON ta.test_id = t.id
    GROUP BY t.id, e.full_name
    ORDER BY t.started_at DESC
    LIMIT 20
");
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For chart: count of tests per day (last 14 days)
$stmt2 = $pdo->query("SELECT to_char(started_at::date,'YYYY-MM-DD') as day, count(*) as cnt
    FROM tests
    WHERE started_at >= NOW() - INTERVAL '14 days'
    GROUP BY day
    ORDER BY day
");
$series = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard - Testing System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
  <div class="container">
    <a class="navbar-brand" href="#">Test System</a>
    <div class="d-flex">
      <span class="me-3">Привет, <?=htmlspecialchars($user['full_name'])?></span>
      <a class="btn btn-outline-secondary btn-sm" href="questions.php">Вопросы</a>
      <a class="btn btn-outline-danger btn-sm ms-2" href="logout.php">Выйти</a>
    </div>
  </div>
</nav>
<div class="container">
  <div class="row mb-4">
    <div class="col-md-6">
      <div class="card p-3">
        <h5>Последние прохождения</h5>
        <table class="table table-sm">
          <thead><tr><th>#</th><th>Сотрудник</th><th>Правильных</th><th>Всего</th><th>Дата</th></tr></thead>
          <tbody>
            <?php foreach($tests as $t): ?>
              <tr>
                <td><?=htmlspecialchars($t['id'])?></td>
                <td><?=htmlspecialchars($t['full_name'])?></td>
                <td><?=htmlspecialchars($t['correct'] ?? 0)?></td>
                <td><?=htmlspecialchars($t['total'] ?? 0)?></td>
                <td><?=htmlspecialchars($t['started_at'])?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-3">
        <h5>Тесты за последние 14 дней</h5>
        <canvas id="testsChart" height="150"></canvas>
      </div>
    </div>
  </div>
  <div class="mb-4">
    <a href="start_test.php" class="btn btn-success">Начать тест</a>
  </div>
</div>
<script>
  const labels = <?= json_encode(array_column($series, 'day')) ?>;
  const data = <?= json_encode(array_map(function($r){ return (int)$r['cnt']; }, $series)) ?>;

  const ctx = document.getElementById('testsChart').getContext('2d');

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{ label: 'Тесты', data: data }]
    },
    options: {}
  });
</script>
</body>
</html>
