<?php
require_once __DIR__ . '/../app/auth.php';
require_login();
$user = current_user();
require_once __DIR__ . '/../app/db.php';

// Choose 10 random questions (or less if not enough)
$limit = 10;
$stmt = $pdo->prepare('SELECT id, text FROM questions ORDER BY RANDOM() LIMIT :lim');
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();
$qs = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($qs) == 0) {
    echo "<p>Нет вопросов для теста. Добавьте вопросы.</p><p><a href='questions.php'>К вопросам</a></p>";
    exit;
}
// Insert test
$stmt = $pdo->prepare('INSERT INTO tests (employee_id) VALUES (:eid) RETURNING id');
$stmt->execute([':eid'=>$user['id']]);
$testId = $stmt->fetchColumn();
// Store question ids in session for the test
$_SESSION['test_id'] = $testId;
$_SESSION['test_qs'] = array_column($qs, 'id');
header('Location: test.php');
exit;
