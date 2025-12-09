<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../app/auth.php';
require_login();
$user = current_user();
require_once __DIR__ . '/../app/db.php';

$error = '';
$post_text = $_POST['question_text'] ?? '';
$post_answers = $_POST['answers'] ?? [];
$correct = $_POST['correct'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (trim($post_text) === '') {
        $error = 'Введите текст вопроса.';
    } elseif (count($post_answers) < 2) {
        $error = 'Добавьте минимум два варианта ответа.';
    } elseif ($correct === null) {
        $error = 'Выберите правильный ответ.';
    } else {
        // Сохраняем в БД
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO questions (text, created_by) VALUES (:t, :u) RETURNING id');
        $stmt->execute(['t'=>$post_text, 'u'=>$user['id']]);
        $qid = $stmt->fetchColumn();

        $ins = $pdo->prepare("INSERT INTO answers (question_id, text, is_correct) VALUES (:q, :t, :c)");

        foreach ($post_answers as $i => $ansTxt) {
    $ansTxt = trim($ansTxt);
    if ($ansTxt === '') continue;

    $ins->execute([
        'q' => $qid,
        't' => $ansTxt,
        'c' => ($i == $correct ? 1 : 0) // ← ВАЖНО: преобразуем в 1/0
    ]);
}

        $pdo->commit();
        header("Location: questions.php");
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Создать вопрос</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<script>
function addAnswer() {
    const list = document.getElementById('answers');
    const index = list.children.length;

    const div = document.createElement('div');
    div.className = 'input-group mb-2';

    div.innerHTML = `
        <div class="input-group-text">
            <input type="radio" name="correct" value="${index}">
        </div>
        <input type="text" class="form-control" name="answers[]" placeholder="Вариант ${index+1}">
        <button type="button" class="btn btn-outline-danger" onclick="this.parentNode.remove(); renumberAnswers();">X</button>
    `;

    list.appendChild(div);
}

function renumberAnswers() {
    const list = document.getElementById('answers').children;

    Array.from(list).forEach((div, i) => {
        let radio = div.querySelector('input[type=radio]');
        let text = div.querySelector('input[type=text]');

        radio.value = i;
        text.placeholder = "Вариант " + (i+1);
    });
}
</script>

</head>
<body>
<div class="container py-4">
<h3>Создание вопроса</h3>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post">

  <div class="mb-3">
    <label class="form-label">Текст вопроса</label>
    <textarea name="question_text" class="form-control" rows="3"><?= htmlspecialchars($post_text) ?></textarea>
  </div>

  <h5>Варианты ответа</h5>

  <div id="answers">
    <?php
      if (count($post_answers) === 0) {
          $post_answers = ["", ""]; // минимум 2 варианта по умолчанию
      }

      foreach ($post_answers as $i => $val):
    ?>
      <div class="input-group mb-2">
        <div class="input-group-text">
          <input type="radio" name="correct" value="<?= $i ?>" <?= ($correct == $i ? 'checked' : '') ?>>
        </div>
        <input type="text" name="answers[]" class="form-control" 
               value="<?= htmlspecialchars($val) ?>"
               placeholder="Вариант <?= $i+1 ?>">
        <button type="button" class="btn btn-outline-danger" onclick="this.parentNode.remove(); renumberAnswers();">X</button>
      </div>
    <?php endforeach; ?>
  </div>

  <button type="button" class="btn btn-outline-primary mt-2" onclick="addAnswer()">Добавить вариант</button>

  <hr>
  <button class="btn btn-primary">Сохранить</button>
  <a href="questions.php" class="btn btn-secondary">Назад</a>

</form>
</div>
</body>
</html>
