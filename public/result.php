<?php
require_once __DIR__ . '/../app/auth.php';
require_login();
require_once __DIR__ . '/../app/db.php';

$testId = $_SESSION['test_id'] ?? null;
if (!$testId)
    die("Нет активного теста");

$user = current_user();

// Загружаем результаты
$sql = "
SELECT 
    q.id AS qid, 
    q.text AS question,
    a.text AS answer,
    ta.answer_id,
    ta.is_correct,
    ca.text AS correct_answer
FROM test_answers ta
JOIN questions q ON q.id = ta.question_id
LEFT JOIN answers a ON a.id = ta.answer_id
LEFT JOIN answers ca ON ca.question_id = q.id AND ca.is_correct = true
WHERE ta.test_id = :tid
ORDER BY q.id
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['tid' => $testId]);
$rows = $stmt->fetchAll();

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Результаты теста</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <h3>Результаты</h3>

<?php
$correctCount = 0;
foreach ($rows as $r) {
    if ($r['is_correct']) $correctCount++;
}
?>

  <div class="alert alert-info">
    Правильных ответов: <b><?= $correctCount ?></b> из <b><?= count($rows) ?></b>
  </div>

  <?php foreach ($rows as $r): ?>
    <div class="card mb-3 p-3">
      <h5><?= htmlspecialchars($r['question']) ?></h5>

      <p>
        Ваш ответ:
        <?php if ($r['answer']): ?>
          <b class="<?= $r['is_correct'] ? 'text-success' : 'text-danger' ?>">
            <?= htmlspecialchars($r['answer']) ?>
          </b>
        <?php else: ?>
          <span class="text-warning">Не выбрано</span>
        <?php endif; ?>
      </p>

      <?php if (!$r['is_correct']): ?>
        <p>Правильный ответ: <b class="text-success"><?= htmlspecialchars($r['correct_answer']) ?></b></p>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <a href="dashboard.php" class="btn btn-primary">Назад</a>
</div>
</body>
</html>
