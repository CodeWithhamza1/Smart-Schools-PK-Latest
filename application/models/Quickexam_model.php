<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Quickexam_model extends CI_Model
{
    private $quick_exam_group_name = 'Teacher Quick Tests';

    public function __construct()
    {
        parent::__construct();
    }

    public function getOrCreateQuickExamGroup($exam_type = 'basic_system')
    {
        $this->db->where('name', $this->quick_exam_group_name);
        $query = $this->db->get('exam_groups');
        if ($query->num_rows() > 0) {
            return (int) $query->row()->id;
        }

        $insert = array(
            'name'        => $this->quick_exam_group_name,
            'exam_type'   => $exam_type,
            'is_active'   => 1,
            'description' => 'Auto-created group for simplified teacher tests',
        );
        $this->db->insert('exam_groups', $insert);
        return (int) $this->db->insert_id();
    }

    public function getClassStudents($class_id, $section_id, $session_id)
    {
        $this->db->select('student_session.id as student_session_id, student_session.student_id');
        $this->db->from('student_session');
        $this->db->join('students', 'students.id = student_session.student_id');
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where('student_session.section_id', $section_id);
        $this->db->where('student_session.session_id', $session_id);
        $this->db->where('students.is_active', 'yes');
        return $this->db->get()->result_array();
    }

    public function createQuickTest($data)
    {
        $this->db->trans_begin();

        $this->db->insert('exam_group_class_batch_exams', $data['exam']);
        $exam_id = (int) $this->db->insert_id();

        $exam_subject_ids = array();
        $exam_subjects = isset($data['exam_subjects']) ? $data['exam_subjects'] : array();
        if (empty($exam_subjects) && isset($data['exam_subject'])) {
            $exam_subjects[] = $data['exam_subject'];
        }
        foreach ($exam_subjects as $subject_row) {
            $subject_row['exam_group_class_batch_exams_id'] = $exam_id;
            $this->db->insert('exam_group_class_batch_exam_subjects', $subject_row);
            $exam_subject_ids[] = (int) $this->db->insert_id();
        }

        if (!empty($data['exam_students'])) {
            $exam_students = array();
            foreach ($data['exam_students'] as $student) {
                $student['exam_group_class_batch_exam_id'] = $exam_id;
                $exam_students[] = $student;
            }
            $this->db->insert_batch('exam_group_class_batch_exam_students', $exam_students);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return array('status' => false);
        }

        $this->db->trans_commit();
        return array(
            'status'          => true,
            'exam_id'         => $exam_id,
            'exam_subject_id' => !empty($exam_subject_ids) ? $exam_subject_ids[0] : 0,
            'exam_subject_ids'=> $exam_subject_ids,
        );
    }

    public function publishQuickTest($exam_id)
    {
        $this->db->where('id', $exam_id);
        return $this->db->update('exam_group_class_batch_exams', array('is_publish' => 1, 'is_active' => 1));
    }

    public function getQuickExams($session_id)
    {
        $this->db->select('exam_group_class_batch_exams.id, exam_group_class_batch_exams.exam, exam_group_class_batch_exams.date_from, exam_group_class_batch_exams.is_publish');
        $this->db->from('exam_group_class_batch_exams');
        $this->db->join('exam_groups', 'exam_groups.id = exam_group_class_batch_exams.exam_group_id');
        $this->db->where('exam_groups.name', $this->quick_exam_group_name);
        $this->db->where('exam_group_class_batch_exams.session_id', $session_id);
        $this->db->order_by('exam_group_class_batch_exams.id', 'desc');
        return $this->db->get()->result_array();
    }

    public function getExamSubjects($exam_id)
    {
        $this->db->select('exam_group_class_batch_exam_subjects.id, exam_group_class_batch_exam_subjects.max_marks, exam_group_class_batch_exam_subjects.min_marks, subjects.name as subject_name, subjects.id as subject_id');
        $this->db->from('exam_group_class_batch_exam_subjects');
        $this->db->join('subjects', 'subjects.id = exam_group_class_batch_exam_subjects.subject_id');
        $this->db->where('exam_group_class_batch_exam_subjects.exam_group_class_batch_exams_id', $exam_id);
        $this->db->order_by('subjects.name', 'asc');
        return $this->db->get()->result_array();
    }

    public function getExamClassSectionSession($exam_id)
    {
        $this->db->select('student_session.class_id, student_session.section_id, student_session.session_id');
        $this->db->from('exam_group_class_batch_exam_students');
        $this->db->join('student_session', 'student_session.id = exam_group_class_batch_exam_students.student_session_id');
        $this->db->where('exam_group_class_batch_exam_students.exam_group_class_batch_exam_id', $exam_id);
        $this->db->group_by(array('student_session.class_id', 'student_session.section_id', 'student_session.session_id'));
        $this->db->limit(1);
        return $this->db->get()->row_array();
    }

    public function getDashboardWidgetData($session_id, $limit = 6)
    {
        $limit = (int) $limit;
        if ($limit <= 0) {
            $limit = 6;
        }

        $sql = "SELECT
                    exam_group_class_batch_exams.id,
                    exam_group_class_batch_exams.exam,
                    exam_group_class_batch_exams.date_from,
                    student_session.class_id,
                    student_session.section_id,
                    classes.class AS class_name,
                    sections.section AS section_name,
                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM exam_group_class_batch_exam_subjects
                            INNER JOIN exam_group_exam_results
                                ON exam_group_exam_results.exam_group_class_batch_exam_subject_id = exam_group_class_batch_exam_subjects.id
                            WHERE exam_group_class_batch_exam_subjects.exam_group_class_batch_exams_id = exam_group_class_batch_exams.id
                            LIMIT 1
                        ) THEN 1
                        ELSE 0
                    END AS has_result_entry
                FROM exam_group_class_batch_exams
                INNER JOIN exam_groups
                    ON exam_groups.id = exam_group_class_batch_exams.exam_group_id
                INNER JOIN exam_group_class_batch_exam_students
                    ON exam_group_class_batch_exam_students.exam_group_class_batch_exam_id = exam_group_class_batch_exams.id
                INNER JOIN student_session
                    ON student_session.id = exam_group_class_batch_exam_students.student_session_id
                INNER JOIN classes
                    ON classes.id = student_session.class_id
                INNER JOIN sections
                    ON sections.id = student_session.section_id
                WHERE exam_groups.name = ?
                    AND exam_group_class_batch_exams.session_id = ?
                GROUP BY
                    exam_group_class_batch_exams.id,
                    exam_group_class_batch_exams.exam,
                    exam_group_class_batch_exams.date_from,
                    student_session.class_id,
                    student_session.section_id,
                    classes.class,
                    sections.section
                ORDER BY exam_group_class_batch_exams.date_from DESC, exam_group_class_batch_exams.id DESC";

        $rows = $this->db->query($sql, array($this->quick_exam_group_name, $session_id))->result_array();

        if (empty($rows)) {
            return array(
                'rows' => array(),
                'total_tests' => 0,
                'completed_tests' => 0,
                'pending_tests' => 0,
            );
        }

        $class_totals = array();
        foreach ($rows as $row) {
            $class_key = (int) $row['class_id'] . '-' . (int) $row['section_id'];
            if (!isset($class_totals[$class_key])) {
                $class_totals[$class_key] = array(
                    'total_tests' => 0,
                    'completed_tests' => 0,
                );
            }

            $class_totals[$class_key]['total_tests']++;
            $class_totals[$class_key]['completed_tests'] += (int) $row['has_result_entry'];
        }

        $widget_rows = array();
        $total_tests = count($rows);
        $completed_tests = 0;

        foreach ($rows as $row) {
            $completed_tests += (int) $row['has_result_entry'];
        }

        foreach ($rows as $row) {
            $class_key = (int) $row['class_id'] . '-' . (int) $row['section_id'];
            $summary = $class_totals[$class_key];
            $pending_tests = max(0, (int) $summary['total_tests'] - (int) $summary['completed_tests']);

            $widget_rows[] = array(
                'exam_id' => (int) $row['id'],
                'event_name' => $row['exam'],
                'class_label' => trim($row['class_name'] . ' - ' . $row['section_name']),
                'total_tests' => (int) $summary['total_tests'],
                'completed_tests' => (int) $summary['completed_tests'],
                'pending_tests' => $pending_tests,
                'has_result_entry' => (int) $row['has_result_entry'],
            );

            if (count($widget_rows) >= $limit) {
                break;
            }
        }

        return array(
            'rows' => $widget_rows,
            'total_tests' => $total_tests,
            'completed_tests' => $completed_tests,
            'pending_tests' => max(0, $total_tests - $completed_tests),
        );
    }
}
