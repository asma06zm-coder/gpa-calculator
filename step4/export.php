<?php
require 'db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { echo "Invalid ID."; exit; }

$stmt = $pdo->prepare("SELECT student, semester, gpa FROM calculations WHERE id = ?");
$stmt->execute([$id]);
$calc = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$calc) { echo "Not found."; exit; }

$stmt2 = $pdo->prepare("SELECT course_name, credits, grade, grade_points FROM courses WHERE calculation_id = ?");
$stmt2->execute([$id]);
$courses = $stmt2->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="gpa_' . $id . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Student', 'Semester', 'GPA']);
fputcsv($out, [$calc['student'], $calc['semester'], $calc['gpa']]);
fputcsv($out, []);
fputcsv($out, ['Course', 'Credits', 'Grade', 'Grade Points']);
foreach ($courses as $c) {
    fputcsv($out, [$c['course_name'], $c['credits'], $c['grade'], $c['grade_points']]);
}
fclose($out);
exit;
?>
