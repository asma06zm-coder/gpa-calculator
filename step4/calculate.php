<?php
header('Content-Type: application/json');
require 'db.php';

if (isset($_POST['course'], $_POST['credits'], $_POST['grade'], $_POST['student'], $_POST['semester'])) {

    $student  = htmlspecialchars(trim($_POST['student']));
    $semester = htmlspecialchars(trim($_POST['semester']));
    $courses  = $_POST['course'];
    $credits  = $_POST['credits'];
    $grades   = $_POST['grade'];

    if (empty($student) || empty($semester)) {
        echo json_encode(['success' => false, 'message' => 'Student name and semester are required.']);
        exit;
    }

    $totalPoints  = 0;
    $totalCredits = 0;
    $rows         = [];
    $usedCourses  = [];

    $tableHtml  = '<table class="table table-bordered mt-3">';
    $tableHtml .= '<thead class="thead-dark">
                    <tr>
                        <th>Course</th><th>Credits</th>
                        <th>Grade</th><th>Grade Points</th>
                    </tr>
                   </thead><tbody>';

    for ($i = 0; $i < count($courses); $i++) {
        $course = htmlspecialchars(trim($courses[$i]));
        $cr     = floatval($credits[$i]);
        $g      = floatval($grades[$i]);

        if ($cr <= 0 || $cr > 10) {
            echo json_encode(['success' => false, 'message' => "Invalid credits for course: $course. Max is 10."]);
            exit;
        }
        if (in_array(strtolower($course), $usedCourses)) {
            echo json_encode(['success' => false, 'message' => "Duplicate course name: $course."]);
            exit;
        }
        $usedCourses[] = strtolower($course);

        $pts           = $cr * $g;
        $totalPoints  += $pts;
        $totalCredits += $cr;
        $rows[]        = ['course' => $course, 'cr' => $cr, 'g' => $g, 'pts' => $pts];

        $tableHtml .= "<tr>
                        <td>$course</td><td>$cr</td>
                        <td>$g</td><td>$pts</td>
                       </tr>";
    }
    $tableHtml .= '</tbody></table>';

    if ($totalCredits > 0) {
        $gpa = $totalPoints / $totalCredits;
        if      ($gpa >= 3.7) $interpretation = "Distinction";
        elseif  ($gpa >= 3.0) $interpretation = "Merit";
        elseif  ($gpa >= 2.0) $interpretation = "Pass";
        else                  $interpretation = "Fail";

        $stmt = $pdo->prepare("INSERT INTO calculations (student, semester, gpa) VALUES (?, ?, ?)");
        $stmt->execute([$student, $semester, $gpa]);
        $calcId = $pdo->lastInsertId();

        $stmt2 = $pdo->prepare("INSERT INTO courses (calculation_id, course_name, credits, grade, grade_points) VALUES (?, ?, ?, ?, ?)");
        foreach ($rows as $r) {
            $stmt2->execute([$calcId, $r['course'], $r['cr'], $r['g'], $r['pts']]);
        }

        $message = "Your GPA is " . number_format($gpa, 2) . " ($interpretation).";
        echo json_encode([
            'success'        => true,
            'gpa'            => $gpa,
            'interpretation' => $interpretation,
            'message'        => $message,
            'tableHtml'      => $tableHtml,
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No valid courses entered.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Data not received.']);
}
exit;
?>
