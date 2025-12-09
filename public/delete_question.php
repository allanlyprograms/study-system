<?php
require_once __DIR__ . '/../app/auth.php';
require_login();
require_once __DIR__ . '/../app/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die("Некорректный ID");

$pdo->beginTransaction();

// Удаляем ответы
$pdo->prepare("DELETE FROM answers WHERE question_id = :id")->execute(['id'=>$id]);

// Удаляем сам вопрос
$pdo->prepare("DELETE FROM questions WHERE id = :id")->execute(['id'=>$id]);

$pdo->commit();

header("Location: questions.php");
exit;
