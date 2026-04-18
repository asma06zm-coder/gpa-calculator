<?php
header('Content-Type: application/json');
require 'db.php';

$stmt = $pdo->query("SELECT id, student, semester, gpa, created_at FROM calculations ORDER BY created_at DESC LIMIT 20");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['success' => true, 'history' => $rows]);
exit;
?>
