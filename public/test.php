<?php
require_once __DIR__ . '/../app/auth.php';
require_login();
$user = current_user();
require_once __DIR__ . '/../app/db.php';

// Безопасно обрабатываем тест в сессии
if (empty($_SESSION['test_id']) || empty($_SESSION['test_qs']) || !is_array($_SESSION['test_qs'])) {
    header('Location: start_test.php');
    exit;
}

$testId = (int)$_SESSION['test_id'];
$qids = array_map('intval', $_SESSION['test_qs']);

// ------ Загрузка вопросов и ответов ------
if (count($qids) === 0) {
    die("Нет вопросов для теста.");
}

$placeholders = implode(',', array_fill(0, count($qids), '?'));

$sql = "
    SELECT 
        q.id  AS qid,
        q.text AS qtext,
        a.id  AS aid,
        a.text AS atext
    FROM questions q
    JOIN answers a ON a.question_id = q.id
    WHERE q.id IN ($placeholders)
    ORDER BY q.id, a.id
";

$stmt = $pdo->prepare($sql);
$stmt->execute($qids);
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Группируем вручную (FETCH_GROUP нестабилен)
$rows = [];
foreach ($all as $row) {
    $qid = $row['qid'];
    if (!isset($rows[$qid])) {
        $rows[$qid] = [
            'question' => [
                'qid' => $row['qid'],
                'qtext' => $row['qtext']
            ],
            'answers' => []
        ];
    }
    $rows[$qid]['answers'][] = [
        'aid' => $row['aid'],
        'atext' => $row['atext']
    ];
}

// ------ Обработка POST (ответы) ------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    foreach ($rows as $qid => $bundle) {

        $postKey = 'answer_' . $qid;
        $aid = isset($_POST[$postKey]) ? (int)$_POST[$postKey] : null;

        // Определяем правильность
        $isCorrect = null;
        if ($aid !== null) {
            $check = $pdo->prepare("SELECT is_correct FROM answers WHERE id = :id");
            $check->execute([':id' => $aid]);
            $isCorrect = $check->fetchColumn() ? 't' : 'f';
        }

        // Запись ответа
        $ins = $pdo->prepare("
            INSERT INTO test_answers (test_id, question_id, answer_id, is_correct)
            VALUES (:tid, :qid, :aid, :corr)
        ");

        $ins->execute([
            ':tid'  => $testId,
            ':qid'  => $qid,
            ':aid'  => $aid,
            ':corr' => $isCorrect
        ]);
    }

    // Закрываем тест
    $pdo->prepare("UPDATE tests SET finished_at = NOW() WHERE id = :id")
        ->execute([':id' => $testId]);

    header('Location: result.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Test - Testing System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <h3>Тест</h3>

  <form method="post">

    <?php foreach ($rows as $bundle): ?>
        <?php $q = $bundle['question']; ?>
        <div class="card mb-3 p-3">
            <h5><?= htmlspecialchars($q['qtext'], ENT_QUOTES, 'UTF-8') ?></h5>

            <?php foreach ($bundle['answers'] as $ans): ?>
                <div class="form-check">
                    <input class="form-check-input"
                           type="radio"
                           name="answer_<?= $q['qid'] ?>"
                           id="a<?= $ans['aid'] ?>"
                           value="<?= $ans['aid'] ?>">
                    <label class="form-check-label" for="a<?= $ans['aid'] ?>">
                        <?= htmlspecialchars($ans['atext'], ENT_QUOTES, 'UTF-8') ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <button class="btn btn-primary">Отправить</button>
  </form>

</div>
</body>
</html>
