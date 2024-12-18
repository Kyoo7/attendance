<?php
$lecturer_pass = password_hash('lecturer123', PASSWORD_DEFAULT);
$student_pass = password_hash('student123', PASSWORD_DEFAULT);

echo "Lecturer password hash: " . $lecturer_pass . "\n";
echo "Student password hash: " . $student_pass . "\n";
?>
