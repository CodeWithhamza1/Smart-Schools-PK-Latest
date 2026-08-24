-- Quick Examination module schema/bootstrap
-- This module intentionally reuses the standard exam-group tables already
-- present in the same-source application. It does not create a parallel
-- exam/result schema.

START TRANSACTION;

INSERT INTO exam_groups (name, exam_type, is_active, description)
SELECT 'Teacher Quick Tests', 'basic_system', 1,
       'Auto-created group for simplified teacher tests'
WHERE NOT EXISTS (
    SELECT 1 FROM exam_groups WHERE name = 'Teacher Quick Tests'
);

COMMIT;

-- Required existing tables (verify before installation):
-- exam_groups
-- exam_group_class_batch_exams
-- exam_group_class_batch_exam_subjects
-- exam_group_class_batch_exam_students
-- exam_group_exam_results
-- subjects, students, student_session, classes, sections

