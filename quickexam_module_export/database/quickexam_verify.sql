-- Run after installation. Every query should return at least one row/column
-- or the indicated table is missing from the target same-source application.

SELECT table_name
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name IN (
    'exam_groups',
    'exam_group_class_batch_exams',
    'exam_group_class_batch_exam_subjects',
    'exam_group_class_batch_exam_students',
    'exam_group_exam_results',
    'subjects', 'students', 'student_session', 'classes', 'sections'
  )
ORDER BY table_name;

SELECT id, name, exam_type, is_active
FROM exam_groups
WHERE name = 'Teacher Quick Tests';

SELECT COUNT(*) AS quick_exam_count
FROM exam_group_class_batch_exams e
INNER JOIN exam_groups g ON g.id = e.exam_group_id
WHERE g.name = 'Teacher Quick Tests';

