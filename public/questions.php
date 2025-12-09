<?php
require_once __DIR__ . '/../app/auth.php';
require_login();
$user = current_user();
require_once __DIR__ . '/../app/db.php';

// Fetch questions with answers
$stmt = $pdo->query('SELECT q.id, q.text, q.created_at, e.full_name FROM questions q JOIN employees e ON e.id = q.created_by ORDER BY q.created_at DESC');
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Questions - Testing System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div class="d-flex mb-3 justify-content-between">
    <h3>Вопросы</h3>
    <div>
      <a class="btn btn-primary" href="create_question.php">Создать вопрос</a>
      <a class="btn btn-outline-secondary ms-2" href="dashboard.php">Назад</a>
    </div>
  </div>
<?php foreach($questions as $q): ?>
    <div class="card mb-2">
      <div class="card-body">
        <h5><?=htmlspecialchars($q['text'])?></h5>

        <div class="small text-muted">
          Создал: <?=htmlspecialchars($q['full_name'])?> — <?=htmlspecialchars($q['created_at'])?>
        </div>

        <!-- Кнопка удалить -->
        <a href="delete_question.php?id=<?= $q['id'] ?>" 
           class="btn btn-sm btn-danger mt-2"
           onclick="return confirm('Удалить вопрос и все его ответы?')">
           Удалить
        </a>

        <?php
          $stmt = $pdo->prepare('SELECT id, text, is_correct FROM answers WHERE question_id = :qid');
          $stmt->execute([':qid'=>$q['id']]);
          $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <ul class="mt-2">
        <?php foreach($answers as $a): ?>
          <li>
            <?=htmlspecialchars($a['text'])?>
            <?php if($a['is_correct']): ?>
              <span class="badge bg-success">правильный</span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
        </ul>
      </div>
    </div>
<?php endforeach; ?>

</div>
</body>
</html>
