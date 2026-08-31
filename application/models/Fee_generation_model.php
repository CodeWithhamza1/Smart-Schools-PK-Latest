<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Transactional, idempotent fee profile update and monthly fee generation.
 *
 * This model deliberately writes to the existing fee tables so collection,
 * challan, report and accounting code continue to use their current contract.
 */
class Fee_generation_model extends MY_Model
{
    private $current_session;

    public function __construct()
    {
        parent::__construct();
        $this->current_session = (int) $this->setting_model->getCurrentSession();
    }

    public function getWorkspaceStudents($class_id, $section_id, $year, $month)
    {
        $class_id  = (int) $class_id;
        $section_id = (int) $section_id;
        $year       = (int) $year;
        $month      = (int) $month;

        $this->db->select('student_session.id AS student_session_id, student_session.class_id, student_session.section_id,
            students.id AS student_id, students.admission_no, students.firstname, students.middlename,
            students.lastname, students.father_name, students.admission_date, students.monthly_fee,
            students.admission_fee, students.discount_fee, students.final_fee, classes.class, sections.section');
        $this->db->from('student_session');
        $this->db->join('students', 'students.id = student_session.student_id');
        $this->db->join('classes', 'classes.id = student_session.class_id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('student_session.class_id', $class_id);
        $this->db->where('students.is_active', 'yes');
        if ($section_id > 0) {
            $this->db->where('student_session.section_id', $section_id);
        }
        $this->db->order_by('students.firstname', 'asc');
        $this->db->order_by('students.id', 'asc');
        $students = $this->db->get()->result_array();

        if (empty($students)) {
            return array();
        }

        $session_ids = array();
        foreach ($students as $student) {
            $session_ids[] = (int) $student['student_session_id'];
        }
        $fee_status = $this->getExistingFeeStatus($session_ids, $year, $month);

        foreach ($students as &$student) {
            $student_session_id = (int) $student['student_session_id'];
            $student['monthly_fee']   = (float) $student['monthly_fee'];
            $student['admission_fee'] = (float) $student['admission_fee'];
            $student['discount_fee']  = (float) $student['discount_fee'];
            $student['final_fee']     = (float) $student['final_fee'];
            $student['eligible']      = $this->isStudentEligible($student['admission_date'], $year, $month);
            $student['fees']          = isset($fee_status[$student_session_id]) ? $fee_status[$student_session_id] : array();
        }
        unset($student);

        return $students;
    }

    public function updateDueDates(array $payload, $actor_id, $request_id)
    {
        $run = $this->startRun($request_id, 'due_date', $payload, $actor_id);
        if (isset($run['duplicate']) && $run['duplicate']) {
            return $run['result'];
        }

        $counts = $this->emptyCounts();
        $errors = array();
        $scope_lock = null;

        try {
            $this->db->trans_begin();
            $scope_lock = $this->acquireScopeLock($payload);
            $students = $this->validatedSelectedStudents($payload);
            $fees = $this->normaliseFees($payload['fees']);
            $rows = $this->getExistingFeeRows(array_keys($students), $payload['year'], $payload['month'], array_keys($fees), true);
            $by_key = array();
            foreach ($rows as $row) {
                $key = (int) $row['student_session_id'] . ':' . (int) $row['feetype_id'];
                $by_key[$key][] = $row;
            }

            $updates = array();
            foreach ($fees as $fee_type_id => $fee) {
                foreach ($this->feeTargetIds($fee, $students) as $student_session_id) {
                    $key = $student_session_id . ':' . $fee_type_id;
                    if (empty($by_key[$key])) {
                        $counts['skipped']++;
                        continue;
                    }
                    if (count($by_key[$key]) !== 1 || !empty($by_key[$key][0]['deposit_id']) || $this->hasSettlement($by_key[$key][0]['amount_detail'])) {
                        $counts['locked']++;
                        $errors[] = $students[$student_session_id]['admission_no'] . ' - ' . $fee['type'] . ' is protected; its due date was not changed.';
                        continue;
                    }
                    $updates[] = array('id' => (int) $by_key[$key][0]['fee_groups_feetype_id'], 'due_date' => $payload['due_date']);
                }
            }

            foreach (array_chunk($updates, 200) as $chunk) {
                if ($this->db->update_batch('fee_groups_feetype', $chunk, 'id') === false) {
                    throw new Exception('Unable to update fee due dates.');
                }
            }
            $counts['updated_fee_rows'] = count($updates);
            $counts['total'] = count($students);
            $result = array('status' => 'success', 'run_id' => $request_id, 'counts' => $counts, 'errors' => $errors);
            $this->finishRun($request_id, 'completed', $result);
            if ($this->db->trans_status() === false || $this->db->trans_commit() === false) {
                throw new Exception('The due-date transaction failed.');
            }
            $this->releaseScopeLock($scope_lock);
            return $result;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->releaseScopeLock($scope_lock);
            $counts['failed']++;
            $result = array('status' => 'fail', 'run_id' => $request_id, 'counts' => $counts, 'errors' => array($e->getMessage()));
            $this->finishRun($request_id, 'failed', $result);
            return $result;
        }
    }

    private function updateFees(array $payload, $actor_id, $request_id)
    {
        $run = $this->startRun($request_id, 'update', $payload, $actor_id);
        if (isset($run['duplicate']) && $run['duplicate']) {
            return $run['result'];
        }

        $counts = $this->emptyCounts();
        $errors = array();
        $scope_lock = null;

        try {
            $this->db->trans_begin();
            $scope_lock = $this->acquireScopeLock($payload);
            $students = $this->validatedSelectedStudents($payload);
            $fees     = $this->normaliseFees($payload['fees']);
            $year     = (int) $payload['year'];
            $month    = (int) $payload['month'];
            $due_date = $payload['due_date'];
            $posted   = $this->postedStudentMap($payload['students']);

            $student_updates = array();
            foreach ($students as $student_session_id => $student) {
                $values = $posted[$student_session_id];
                $monthly = $this->money($values['monthly_fee']);
                $admission = $this->money($values['admission_fee']);
                $discount = $this->money($values['discount_fee']);
                if ($discount > $monthly) {
                    throw new Exception('Discount cannot be greater than monthly fee for admission no. ' . $student['admission_no'] . '.');
                }
                $student_updates[] = array(
                    'id'            => (int) $student['student_id'],
                    'monthly_fee'   => $monthly,
                    'admission_fee' => $admission,
                    'discount_fee'  => $discount,
                    'final_fee'     => $monthly - $discount,
                );
                $students[$student_session_id]['monthly_fee']   = $monthly;
                $students[$student_session_id]['admission_fee'] = $admission;
                $students[$student_session_id]['discount_fee']  = $discount;
                $students[$student_session_id]['overrides']     = isset($values['overrides']) && is_array($values['overrides']) ? $values['overrides'] : array();
            }

            foreach (array_chunk($student_updates, 200) as $chunk) {
                if ($this->db->update_batch('students', $chunk, 'id') === false) {
                    throw new Exception('Unable to update student fee profiles.');
                }
            }
            $counts['updated'] += count($student_updates);

            foreach ($students as $student) {
                $this->syncFutureDiscount($student);
            }

            $existing = $this->getExistingFeeRows(array_keys($students), $year, $month, array_keys($fees), true);
            $fee_updates = array();
            $existing_by_key = array();
            foreach ($existing as $row) {
                $key = (int) $row['student_session_id'] . ':' . (int) $row['feetype_id'];
                $existing_by_key[$key][] = $row;
            }
            foreach ($existing_by_key as $rows) {
                $row = $rows[0];
                $student_session_id = (int) $row['student_session_id'];
                $fee_type_id = (int) $row['feetype_id'];
                if (count($rows) > 1) {
                    $counts['locked']++;
                    $errors[] = $students[$student_session_id]['admission_no'] . ' - ' . $row['fee_type'] . ' has legacy duplicate fee rows and was not changed.';
                    continue;
                }
                if ($this->hasSettlement($row['amount_detail'])) {
                    $counts['locked']++;
                    $errors[] = $students[$student_session_id]['admission_no'] . ' - ' . $row['fee_type'] . ' is paid, discounted or partially settled.';
                    continue;
                }
                $updated_amount = $this->feeAmount($students[$student_session_id], $fees[$fee_type_id]);
                if (strcasecmp($row['fee_code'], 'Tuition') === 0
                    && (float) $students[$student_session_id]['discount_fee'] > $updated_amount) {
                    throw new Exception('Discount cannot exceed the Tuition fee for admission no. ' . $students[$student_session_id]['admission_no'] . '.');
                }
                $fee_updates[] = array(
                    'id'       => (int) $row['fee_groups_feetype_id'],
                    'amount'   => $updated_amount,
                    'due_date' => $due_date,
                );
            }

            foreach (array_chunk($fee_updates, 200) as $chunk) {
                if ($this->db->update_batch('fee_groups_feetype', $chunk, 'id') === false) {
                    throw new Exception('Unable to update generated fee rows.');
                }
            }
            $counts['updated_fee_rows'] = count($fee_updates);
            $counts['total'] = count($students);
            $counts['skipped'] = $counts['locked'];

            $result = array('status' => 'success', 'run_id' => $request_id, 'counts' => $counts, 'errors' => $errors);
            $this->finishRun($request_id, 'completed', $result);

            if ($this->db->trans_status() === false) {
                throw new Exception('The fee update transaction failed.');
            }
            if ($this->db->trans_commit() === false) {
                throw new Exception('Unable to commit the fee update transaction.');
            }
            $this->releaseScopeLock($scope_lock);

            return $result;
        } catch (Exception $e) {
            if ($this->db->trans_status() !== null) {
                $this->db->trans_rollback();
            }
            $this->releaseScopeLock($scope_lock);
            $counts['failed']++;
            $result = array('status' => 'fail', 'run_id' => $request_id, 'counts' => $counts, 'errors' => array($e->getMessage()));
            $this->finishRun($request_id, 'failed', $result);
            return $result;
        }
    }

    public function generateFees(array $payload, $actor_id, $request_id)
    {
        $run = $this->startRun($request_id, 'generate', $payload, $actor_id);
        if (isset($run['duplicate']) && $run['duplicate']) {
            return $run['result'];
        }

        $counts = $this->emptyCounts();
        $errors = array();
        $scope_lock = null;

        try {
            $this->db->trans_begin();
            $scope_lock = $this->acquireScopeLock($payload);
            $students = $this->validatedSelectedStudents($payload);
            $fees     = $this->normaliseFees($payload['fees']);
            $year     = (int) $payload['year'];
            $month    = (int) $payload['month'];
            $due_date = $payload['due_date'];
            $posted   = $this->postedStudentMap($payload['students']);

            foreach ($students as $student_session_id => &$student) {
                $values = $posted[$student_session_id];
                $student['monthly_fee']   = $this->money($values['monthly_fee']);
                $student['admission_fee'] = $this->money($values['admission_fee']);
                $student['discount_fee']  = $this->money($values['discount_fee']);
                $student['overrides']     = isset($values['overrides']) && is_array($values['overrides']) ? $values['overrides'] : array();
                if (!$this->isStudentEligible($student['admission_date'], $year, $month)) {
                    $counts['ineligible']++;
                    $errors[] = $student['admission_no'] . ' - admission is after the selected billing month.';
                    unset($students[$student_session_id]);
                }
            }
            unset($student);

            if (empty($students)) {
                throw new Exception('No selected student is eligible for this billing month.');
            }

            $fee_targets = array();
            $targeted_students = array();
            foreach ($fees as $fee_type_id => $fee) {
                $fee_targets[$fee_type_id] = array_flip($this->feeTargetIds($fee, $students));
                foreach ($fee_targets[$fee_type_id] as $student_session_id => $unused) {
                    $targeted_students[$student_session_id] = true;
                }
            }
            $students = array_intersect_key($students, $targeted_students);

            $fee_groups = $this->ensureStudentFeeGroups($students);
            $session_groups = $this->ensureFeeSessionGroups($fee_groups);
            $fee_masters = $this->ensureStudentFeeMasters($students, $fee_groups, $session_groups);
            $existing = $this->getExistingFeeRows(array_keys($students), $year, $month, array_keys($fees), true);
            $existing_keys = array();
            $existing_key_rows = array();
            foreach ($existing as $row) {
                $existing_key = (int) $row['student_session_id'] . ':' . (int) $row['feetype_id'];
                $existing_keys[$existing_key] = true;
                $existing_key_rows[$existing_key][] = $row;
            }

            $inserts = array();
            $created_keys = array();
            foreach ($students as $student_session_id => $student) {
                $fee_group_id = $fee_groups[(int) $student['student_id']];
                $fee_session_group_id = $session_groups[$fee_group_id];
                foreach ($fees as $fee_type_id => $fee) {
                    if (!isset($fee_targets[$fee_type_id][$student_session_id])) {
                        continue;
                    }
                    $key = $student_session_id . ':' . $fee_type_id;
                    if (isset($existing_keys[$key])) {
                        $counts['existing']++;
                        if (count($existing_key_rows[$key]) > 1) {
                            $counts['locked']++;
                            $errors[] = $student['admission_no'] . ' - ' . $fee['type'] . ' has legacy duplicate fee rows; no new row was created.';
                        }
                        continue;
                    }
                    $generated_amount = $this->feeAmount($student, $fee);
                    if (strcasecmp($fee['code'], 'Tuition') === 0
                        && (float) $student['discount_fee'] > $generated_amount) {
                        throw new Exception('Discount cannot exceed the Tuition fee for admission no. ' . $student['admission_no'] . '.');
                    }
                    $inserts[] = array(
                        'fee_session_group_id' => $fee_session_group_id,
                        'fee_groups_id'         => $fee_group_id,
                        'feetype_id'            => $fee_type_id,
                        'amount'                => $generated_amount,
                        'due_date'              => $due_date,
                        'session_id'            => $this->current_session,
                        'clnd_month'            => $month,
                        'year'                  => $year,
                        'Session'               => $this->current_session,
                        'Total_Package'         => 0,
                        'Method'                => 'Monthly',
                        'is_genrated'            => 1,
                    );
                    $created_keys[$key] = true;
                }
            }

            foreach (array_chunk($inserts, 200) as $chunk) {
                if ($this->db->insert_batch('fee_groups_feetype', $chunk) === false) {
                    throw new Exception('Unable to insert generated fee rows.');
                }
            }
            $counts['created'] = count($inserts);
            $counts['total']   = count($students);
            $counts['skipped'] = $counts['existing'] + $counts['ineligible'];

            // Apply a discount marker only to newly created Tuition rows, using
            // the same amount_detail shape consumed by existing reports.
            if (!empty($inserts)) {
                $all_rows = $this->getExistingFeeRows(array_keys($students), $year, $month, array_keys($fees), true);
                $this->assertCreatedFeeKeysUnique($created_keys, $all_rows);
                foreach ($all_rows as $row) {
                    $key = (int) $row['student_session_id'] . ':' . (int) $row['feetype_id'];
                    if (isset($existing_keys[$key]) || strcasecmp($row['fee_code'], 'Tuition') !== 0) {
                        continue;
                    }
                    $student = $students[(int) $row['student_session_id']];
                    if ((float) $student['discount_fee'] > 0) {
                        $this->applyGenerationDiscount($student, $row, $actor_id, $due_date);
                    }
                }
            }

            $result = array('status' => 'success', 'run_id' => $request_id, 'counts' => $counts, 'errors' => $errors);
            $this->finishRun($request_id, 'completed', $result);

            if ($this->db->trans_status() === false) {
                throw new Exception('The fee generation transaction failed.');
            }
            if ($this->db->trans_commit() === false) {
                throw new Exception('Unable to commit the fee generation transaction.');
            }
            $this->releaseScopeLock($scope_lock);

            return $result;
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->releaseScopeLock($scope_lock);
            $counts['failed']++;
            $result = array('status' => 'fail', 'run_id' => $request_id, 'counts' => $counts, 'errors' => array($e->getMessage()));
            $this->finishRun($request_id, 'failed', $result);
            return $result;
        }
    }

    private function validatedSelectedStudents(array $payload)
    {
        $posted = $this->postedStudentMap($payload['students']);
        if (empty($posted)) {
            throw new Exception('Select at least one student.');
        }
        $workspace = $this->getWorkspaceStudents($payload['class_id'], $payload['section_id'], $payload['year'], $payload['month']);
        $valid = array();
        foreach ($workspace as $student) {
            $student_session_id = (int) $student['student_session_id'];
            if (isset($posted[$student_session_id])) {
                $valid[$student_session_id] = $student;
            }
        }
        if (count($valid) !== count($posted)) {
            throw new Exception('One or more students do not belong to the selected current-session class or section.');
        }
        return $valid;
    }

    private function postedStudentMap(array $students)
    {
        $map = array();
        foreach ($students as $student) {
            if (empty($student['selected']) || empty($student['student_session_id'])) {
                continue;
            }
            $map[(int) $student['student_session_id']] = $student;
        }
        return $map;
    }

    private function normaliseFees(array $fees)
    {
        $ids = array();
        foreach ($fees as $fee) {
            if (!empty($fee['feetype_id'])) {
                $ids[] = (int) $fee['feetype_id'];
            }
        }
        $ids = array_values(array_unique($ids));
        if (empty($ids)) {
            throw new Exception('Add at least one fee type.');
        }

        $this->db->select('id, type, code')->from('feetype')->where_in('id', $ids);
        $rows = $this->db->get()->result_array();
        $known = array();
        foreach ($rows as $row) {
            $known[(int) $row['id']] = $row;
        }
        if (count($known) !== count($ids)) {
            throw new Exception('One or more fee types are invalid.');
        }

        $result = array();
        foreach ($fees as $fee) {
            $id = isset($fee['feetype_id']) ? (int) $fee['feetype_id'] : 0;
            if (!$id || isset($result[$id])) {
                continue;
            }
            $result[$id] = array(
                'id'     => $id,
                'type'   => $known[$id]['type'],
                'code'   => $known[$id]['code'],
                'amount' => $this->money(isset($fee['amount']) ? $fee['amount'] : 0),
                'student_session_ids' => isset($fee['student_session_ids']) && is_array($fee['student_session_ids'])
                    ? array_values(array_unique(array_map('intval', $fee['student_session_ids'])))
                    : array(),
            );
        }
        return $result;
    }

    private function feeTargetIds(array $fee, array $students)
    {
        $allowed = array_map('intval', array_keys($students));
        $requested = isset($fee['student_session_ids']) ? array_map('intval', $fee['student_session_ids']) : array();
        $targets = array_values(array_intersect($requested, $allowed));
        if (empty($targets)) {
            throw new Exception($fee['type'] . ' must have at least one selected student.');
        }
        return $targets;
    }

    private function feeAmount(array $student, array $fee)
    {
        $override = isset($student['overrides'][(string) $fee['id']]) ? $student['overrides'][(string) $fee['id']] : null;
        if ($override !== null && $override !== '') {
            return $this->money($override);
        }
        if (strcasecmp($fee['code'], 'Tuition') === 0) {
            return $this->money($student['monthly_fee']);
        }
        if (strcasecmp($fee['code'], 'Admission') === 0) {
            return $this->money($student['admission_fee']);
        }
        return $this->money($fee['amount']);
    }

    private function ensureStudentFeeGroups(array $students)
    {
        $student_ids = array();
        foreach ($students as $student) {
            $student_ids[] = (int) $student['student_id'];
        }
        $this->db->select('fee_groups.id, fee_groups.student_id,
            MAX(CASE WHEN fee_session_groups.session_id = ' . $this->current_session . ' THEN 1 ELSE 0 END) AS has_current_session', false);
        $this->db->from('fee_groups');
        $this->db->join('fee_session_groups', 'fee_session_groups.fee_groups_id = fee_groups.id', 'left');
        $this->db->where('fee_groups.is_system', 0);
        $this->db->where_in('fee_groups.student_id', $student_ids);
        $this->db->group_by(array('fee_groups.id', 'fee_groups.student_id'));
        $this->db->order_by('has_current_session', 'desc');
        $this->db->order_by('fee_groups.id', 'asc');
        $rows = $this->db->get()->result_array();
        $groups = array();
        foreach ($rows as $row) {
            if (!isset($groups[(int) $row['student_id']])) {
                $groups[(int) $row['student_id']] = (int) $row['id'];
            }
        }
        foreach ($students as $student) {
            $student_id = (int) $student['student_id'];
            if (isset($groups[$student_id])) {
                continue;
            }
            $data = array(
                'name'        => trim($student['firstname'] . ' ' . $student['lastname']) . '-' . $student['class'],
                'description' => '',
                'is_active'   => 1,
                'is_system'   => 0,
                'student_id'  => $student_id,
            );
            if (!$this->db->insert('fee_groups', $data)) {
                throw new Exception('Unable to create fee group for ' . $student['admission_no'] . '.');
            }
            $groups[$student_id] = (int) $this->db->insert_id();
        }
        return $groups;
    }

    private function ensureFeeSessionGroups(array $fee_groups)
    {
        $group_ids = array_values($fee_groups);
        $this->db->select('id, fee_groups_id')->from('fee_session_groups')
            ->where('session_id', $this->current_session)->where_in('fee_groups_id', $group_ids);
        $rows = $this->db->get()->result_array();
        $session_groups = array();
        foreach ($rows as $row) {
            if (!isset($session_groups[(int) $row['fee_groups_id']])) {
                $session_groups[(int) $row['fee_groups_id']] = (int) $row['id'];
            }
        }
        foreach ($group_ids as $group_id) {
            if (isset($session_groups[$group_id])) {
                continue;
            }
            if (!$this->db->insert('fee_session_groups', array('fee_groups_id' => $group_id, 'session_id' => $this->current_session))) {
                throw new Exception('Unable to create fee session group.');
            }
            $session_groups[$group_id] = (int) $this->db->insert_id();
        }
        return $session_groups;
    }

    private function ensureStudentFeeMasters(array $students, array $fee_groups, array $session_groups)
    {
        $student_session_ids = array_keys($students);
        $fee_session_group_ids = array_values($session_groups);
        $this->db->select('id, student_session_id, fee_session_group_id')->from('student_fees_master')
            ->where_in('student_session_id', $student_session_ids)->where_in('fee_session_group_id', $fee_session_group_ids);
        $rows = $this->db->get()->result_array();
        $masters = array();
        foreach ($rows as $row) {
            $masters[(int) $row['student_session_id'] . ':' . (int) $row['fee_session_group_id']] = (int) $row['id'];
        }

        $inserts = array();
        foreach ($students as $student_session_id => $student) {
            $group_id = $fee_groups[(int) $student['student_id']];
            $fee_session_group_id = $session_groups[$group_id];
            $key = $student_session_id . ':' . $fee_session_group_id;
            if (!isset($masters[$key])) {
                $inserts[] = array('student_session_id' => $student_session_id, 'fee_session_group_id' => $fee_session_group_id);
            }
        }
        foreach (array_chunk($inserts, 200) as $chunk) {
            if ($this->db->insert_batch('student_fees_master', $chunk) === false) {
                throw new Exception('Unable to assign generated fees to students.');
            }
        }

        if (!empty($inserts)) {
            $this->db->select('id, student_session_id, fee_session_group_id')->from('student_fees_master')
                ->where_in('student_session_id', $student_session_ids)->where_in('fee_session_group_id', $fee_session_group_ids);
            $rows = $this->db->get()->result_array();
            foreach ($rows as $row) {
                $masters[(int) $row['student_session_id'] . ':' . (int) $row['fee_session_group_id']] = (int) $row['id'];
            }
        }
        return $masters;
    }

    private function getExistingFeeRows(array $student_session_ids, $year, $month, array $fee_type_ids, $for_update = false)
    {
        if (empty($student_session_ids) || empty($fee_type_ids)) {
            return array();
        }
        // Query Builder handles IN arrays portably; FOR UPDATE is appended only
        // after all predicates are escaped.
        $this->db->select('sfm.student_session_id, sfm.id AS student_fees_master_id,
            fgft.id AS fee_groups_feetype_id, fgft.feetype_id, ft.type AS fee_type, ft.code AS fee_code,
            fgft.amount, fgft.due_date, sfd.id AS deposit_id, sfd.amount_detail', false);
        $this->db->from('student_fees_master sfm');
        $this->db->join('fee_session_groups fsg', 'fsg.id = sfm.fee_session_group_id');
        $this->db->join('fee_groups_feetype fgft', 'fgft.fee_session_group_id = fsg.id');
        $this->db->join('feetype ft', 'ft.id = fgft.feetype_id');
        $this->db->join('student_fees_deposite sfd', 'sfd.student_fees_master_id = sfm.id AND sfd.fee_groups_feetype_id = fgft.id', 'left');
        $this->db->where_in('sfm.student_session_id', $student_session_ids);
        $this->db->where('fgft.session_id', $this->current_session);
        $this->db->where('fgft.year', (int) $year);
        $this->db->where('fgft.clnd_month', (int) $month);
        $this->db->where_in('fgft.feetype_id', $fee_type_ids);
        $compiled = $this->db->get_compiled_select();
        if ($for_update) {
            $compiled .= ' FOR UPDATE';
        }
        $query = $this->db->query($compiled);
        if ($query === false) {
            throw new Exception('Unable to load existing generated fees.');
        }
        return $query->result_array();
    }

    private function getExistingFeeStatus(array $student_session_ids, $year, $month)
    {
        $status = array();
        foreach (array_chunk($student_session_ids, 500) as $chunk) {
            $this->db->select('sfm.student_session_id, fgft.feetype_id, ft.type, ft.code, fgft.amount,
                fgft.due_date, sfd.amount_detail', false);
            $this->db->from('student_fees_master sfm');
            $this->db->join('fee_session_groups fsg', 'fsg.id = sfm.fee_session_group_id');
            $this->db->join('fee_groups_feetype fgft', 'fgft.fee_session_group_id = fsg.id');
            $this->db->join('feetype ft', 'ft.id = fgft.feetype_id');
            $this->db->join('student_fees_deposite sfd', 'sfd.student_fees_master_id = sfm.id AND sfd.fee_groups_feetype_id = fgft.id', 'left');
            $this->db->where_in('sfm.student_session_id', $chunk);
            $this->db->where('fgft.session_id', $this->current_session);
            $this->db->where('fgft.year', (int) $year);
            $this->db->where('fgft.clnd_month', (int) $month);
            $rows = $this->db->get()->result_array();
            foreach ($rows as $row) {
                $session_id = (int) $row['student_session_id'];
                $fee_type_id = (int) $row['feetype_id'];
                if (!isset($status[$session_id][$fee_type_id])) {
                    $status[$session_id][$fee_type_id] = array(
                    'feetype_id' => (int) $row['feetype_id'],
                    'type'       => $row['type'],
                    'code'       => $row['code'],
                    'amount'     => (float) $row['amount'],
                    'due_date'   => $row['due_date'],
                    'locked'     => $this->hasSettlement($row['amount_detail']),
                        'duplicate'  => false,
                    );
                } else {
                    $status[$session_id][$fee_type_id]['duplicate'] = true;
                    $status[$session_id][$fee_type_id]['locked'] = true;
                }
            }
        }
        foreach ($status as $session_id => $fees) {
            $status[$session_id] = array_values($fees);
        }
        return $status;
    }

    private function hasSettlement($amount_detail)
    {
        if (empty($amount_detail) || $amount_detail === '0') {
            return false;
        }
        $details = json_decode($amount_detail, true);
        if (!is_array($details)) {
            // Malformed legacy payment data must never be treated as editable.
            return true;
        }
        foreach ($details as $detail) {
            if (!is_array($detail)) {
                return true;
            }
            if ((float) (isset($detail['amount']) ? $detail['amount'] : 0) > 0
                || (float) (isset($detail['amount_discount']) ? $detail['amount_discount'] : 0) > 0
                || (float) (isset($detail['amount_fine']) ? $detail['amount_fine'] : 0) > 0) {
                return true;
            }
        }
        return false;
    }

    private function assertCreatedFeeKeysUnique(array $created_keys, array $rows)
    {
        $counts = array();
        foreach ($rows as $row) {
            $key = (int) $row['student_session_id'] . ':' . (int) $row['feetype_id'];
            if (isset($created_keys[$key])) {
                $counts[$key] = isset($counts[$key]) ? $counts[$key] + 1 : 1;
            }
        }
        foreach ($created_keys as $key => $unused) {
            if (!isset($counts[$key]) || $counts[$key] !== 1) {
                throw new Exception('Fee duplication safety check failed. The complete operation was rolled back.');
            }
        }
    }

    private function syncFutureDiscount(array $student)
    {
        $student_session_id = (int) $student['student_session_id'];
        $amount = $this->money($student['discount_fee']);
        $this->db->select('sfd.id, sfd.fees_discount_id, sfd.status, fd.amount')
            ->from('student_fees_discounts sfd')
            ->join('fees_discounts fd', 'fd.id = sfd.fees_discount_id')
            ->where('sfd.student_session_id', $student_session_id)
            ->order_by('sfd.id', 'desc');
        $assignments = $this->db->get()->result_array();

        $editable = null;
        foreach ($assignments as $assignment) {
            if ($assignment['status'] !== 'applied') {
                $editable = $assignment;
                break;
            }
        }

        if ($amount <= 0) {
            if ($editable) {
                $this->db->where('id', $editable['id'])->delete('student_fees_discounts');
            }
            return null;
        }

        if ($editable) {
            $usage = $this->db->where('fees_discount_id', $editable['fees_discount_id'])->count_all_results('student_fees_discounts');
            if ($usage === 1) {
                $this->db->where('id', $editable['fees_discount_id'])->update('fees_discounts', array('amount' => $amount));
                return (int) $editable['id'];
            }
            $this->db->where('id', $editable['id'])->delete('student_fees_discounts');
        }

        $discount_id = $this->createDiscountDefinition($student, $amount);
        if (!$this->db->insert('student_fees_discounts', array(
            'student_session_id' => $student_session_id,
            'fees_discount_id'   => $discount_id,
            'status'             => 'assigned',
        ))) {
            throw new Exception('Unable to assign discount for ' . $student['admission_no'] . '.');
        }
        return (int) $this->db->insert_id();
    }

    private function createDiscountDefinition(array $student, $amount)
    {
        $data = array(
            'session_id' => $this->current_session,
            'name'       => 'Dis-' . $amount . '-' . $student['admission_no'],
            'code'       => $student['admission_no'] . '-' . substr(md5(uniqid('', true)), 0, 8),
            'amount'     => $amount,
        );
        if (!$this->db->insert('fees_discounts', $data)) {
            throw new Exception('Unable to create discount for ' . $student['admission_no'] . '.');
        }
        return (int) $this->db->insert_id();
    }

    private function applyGenerationDiscount(array $student, array $fee_row, $actor_id, $due_date)
    {
        if (!empty($fee_row['deposit_id'])) {
            return;
        }
        $assignment_id = $this->syncFutureDiscount($student);
        if (!$assignment_id) {
            return;
        }
        $detail = array(
            '1' => array(
                'amount'          => 0,
                'date'            => $due_date,
                'amount_discount' => $this->money($student['discount_fee']),
                'amount_fine'     => 0,
                'description'     => 'Generated fee discount',
                'payment_mode'    => 'Cash',
                'received_by'     => (int) $actor_id,
                'inv_no'          => 1,
            ),
        );
        $deposit = array(
            'student_fees_master_id' => (int) $fee_row['student_fees_master_id'],
            'fee_groups_feetype_id'  => (int) $fee_row['fee_groups_feetype_id'],
            'amount_detail'          => json_encode($detail),
        );
        if (!$this->db->insert('student_fees_deposite', $deposit)) {
            throw new Exception('Unable to apply generated discount for ' . $student['admission_no'] . '.');
        }
        $deposit_id = (int) $this->db->insert_id();
        $this->db->where('id', $assignment_id)->update('student_fees_discounts', array(
            'status'      => 'applied',
            'description' => 'Generated fee discount',
            'payment_id'  => $deposit_id . '//1',
        ));
        $this->db->insert('student_fees_discounts_applied', array(
            'student_fees_discounts_id' => $assignment_id,
            'payment_id'                => $deposit_id,
            'description'               => 'Generated fee discount',
        ));
    }

    private function isStudentEligible($admission_date, $year, $month)
    {
        if (empty($admission_date) || $admission_date === '0000-00-00') {
            return true;
        }
        return substr($admission_date, 0, 7) <= sprintf('%04d-%02d', (int) $year, (int) $month);
    }

    private function money($value)
    {
        if ($value === '' || $value === null || !is_numeric($value) || (float) $value < 0) {
            throw new Exception('Fee amounts must be non-negative numbers.');
        }
        return round((float) $value, 2);
    }

    private function emptyCounts()
    {
        return array(
            'created'          => 0,
            'updated'          => 0,
            'updated_fee_rows' => 0,
            'existing'         => 0,
            'locked'           => 0,
            'ineligible'       => 0,
            'skipped'          => 0,
            'failed'           => 0,
            'total'            => 0,
        );
    }

    private function acquireScopeLock(array $payload)
    {
        $name = 'fee_gen:' . $this->current_session . ':' . (int) $payload['class_id'] . ':'
            . (int) $payload['year'] . ':' . (int) $payload['month'];
        $query = $this->db->query('SELECT GET_LOCK(?, 10) AS acquired', array($name));
        $row = $query ? $query->row_array() : null;
        if (!$row || (int) $row['acquired'] !== 1) {
            throw new Exception('Another fee operation is running for this class and month. Please retry after it finishes.');
        }
        return $name;
    }

    private function releaseScopeLock($name)
    {
        if ($name) {
            $this->db->query('SELECT RELEASE_LOCK(?)', array($name));
        }
    }

    public function getGenerationRecords($limit = 500)
    {
        if (!$this->db->table_exists('fee_generation_runs')) {
            return array();
        }
        $select = 'id, request_id, action, staff_id, session_id, class_id, section_id, fee_year, fee_month, status, result_json, error_summary, created_at, updated_at';
        if ($this->db->field_exists('payload_json', 'fee_generation_runs')) {
            $select .= ', payload_json';
        }
        $rows = $this->db->select($select)
            ->from('fee_generation_runs')
            ->order_by('id', 'desc')
            ->limit(max(1, min(1000, (int) $limit)))
            ->get()->result_array();
        foreach ($rows as &$row) {
            $result = !empty($row['result_json']) ? json_decode($row['result_json'], true) : array();
            $row['counts'] = is_array($result) && isset($result['counts']) ? $result['counts'] : array();
            $payload = isset($row['payload_json']) && !empty($row['payload_json']) ? json_decode($row['payload_json'], true) : array();
            $row['student_count'] = 0;
            if (is_array($payload) && !empty($payload['students'])) {
                foreach ($payload['students'] as $student) {
                    if (!empty($student['selected'])) {
                        $row['student_count']++;
                    }
                }
            }
            $row['fee_type_count'] = is_array($payload) && isset($payload['fees']) && is_array($payload['fees']) ? count($payload['fees']) : 0;
        }
        unset($row);
        return $rows;
    }

    public function getChangeFeeRows()
    {
        $this->db->select('fgft.id, fgft.fee_session_group_id, fgft.feetype_id, fgft.amount, fgft.due_date,
            fgft.clnd_month, fgft.year, ft.type AS fee_type, ft.code AS fee_code, fg.id AS fee_group_id,
            fg.student_id, s.admission_no, s.firstname, s.middlename, s.lastname, ss.id AS student_session_id,
            c.class, sec.section, sfd.id AS deposit_id, sfd.amount_detail', false);
        $this->db->from('fee_groups_feetype fgft');
        $this->db->join('feetype ft', 'ft.id = fgft.feetype_id');
        $this->db->join('fee_session_groups fsg', 'fsg.id = fgft.fee_session_group_id');
        $this->db->join('fee_groups fg', 'fg.id = fsg.fee_groups_id');
        $this->db->join('students s', 's.id = fg.student_id', 'left');
        $this->db->join('student_session ss', 'ss.student_id = s.id AND ss.session_id = ' . $this->db->escape($this->current_session), 'left');
        $this->db->join('classes c', 'c.id = ss.class_id', 'left');
        $this->db->join('sections sec', 'sec.id = ss.section_id', 'left');
        $this->db->join('student_fees_master sfm', 'sfm.student_session_id = ss.id AND sfm.fee_session_group_id = fsg.id', 'left');
        $this->db->join('student_fees_deposite sfd', 'sfd.student_fees_master_id = sfm.id AND sfd.fee_groups_feetype_id = fgft.id', 'left');
        $this->db->where('fsg.session_id', $this->current_session);
        $this->db->where('fg.is_system', 0);
        $this->db->order_by('fgft.year', 'desc')->order_by('fgft.clnd_month', 'desc')->order_by('s.admission_no', 'asc')->order_by('fgft.id', 'desc');
        $raw = $this->db->get()->result_array();

        $rows = array();
        $duplicates = array();
        foreach ($raw as $row) {
            $id = (int) $row['id'];
            $key = (int) $row['student_id'] . ':' . (int) $row['feetype_id'] . ':' . (int) $row['year'] . ':' . (int) $row['clnd_month'];
            if (!isset($duplicates[$key])) {
                $duplicates[$key] = array();
            }
            $duplicates[$key][$id] = true;
            if (!isset($rows[$id])) {
                $row['protected'] = false;
                $row['duplicate_key'] = $key;
                $rows[$id] = $row;
            }
            if (!empty($row['deposit_id']) || $this->hasSettlement($row['amount_detail'])) {
                $rows[$id]['protected'] = true;
            }
        }
        foreach ($rows as &$row) {
            $row['duplicate'] = isset($duplicates[$row['duplicate_key']]) && count($duplicates[$row['duplicate_key']]) > 1;
            unset($row['duplicate_key']);
        }
        unset($row);
        return array_values($rows);
    }

    private function startRun($request_id, $action, array $payload, $actor_id)
    {
        if (!$this->db->table_exists('fee_generation_runs')) {
            return array('duplicate' => false);
        }
        $hash = hash('sha256', json_encode($payload));
        $existing = $this->db->where('request_id', $request_id)->get('fee_generation_runs')->row_array();
        if ($existing) {
            if (!hash_equals($existing['payload_hash'], $hash)) {
                throw new Exception('This request ID was already used with different fee data.');
            }
            if ($existing['status'] === 'completed' && !empty($existing['result_json'])) {
                return array('duplicate' => true, 'result' => json_decode($existing['result_json'], true));
            }
            throw new Exception('This fee operation is already being processed.');
        }
        $run_data = array(
            'request_id'   => $request_id,
            'action'       => $action,
            'staff_id'     => (int) $actor_id,
            'session_id'   => $this->current_session,
            'class_id'     => (int) $payload['class_id'],
            'section_id'   => (int) $payload['section_id'],
            'fee_year'     => (int) $payload['year'],
            'fee_month'    => (int) $payload['month'],
            'payload_hash' => $hash,
            'status'       => 'processing',
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        );
        if ($this->db->field_exists('payload_json', 'fee_generation_runs')) {
            $audit_payload = $payload;
            unset($audit_payload['operation_token'], $audit_payload['request_nonce'], $audit_payload['request_id']);
            $run_data['payload_json'] = json_encode($audit_payload);
        }
        $inserted = $this->db->insert('fee_generation_runs', $run_data);
        if (!$inserted) {
            throw new Exception('Unable to reserve this fee operation. Please retry once.');
        }
        return array('duplicate' => false);
    }

    private function finishRun($request_id, $status, array $result)
    {
        if (!$this->db->table_exists('fee_generation_runs')) {
            return;
        }
        $this->db->where('request_id', $request_id)->update('fee_generation_runs', array(
            'status'       => $status,
            'result_json'  => json_encode($result),
            'error_summary'=> $status === 'failed' ? implode(' | ', $result['errors']) : null,
            'updated_at'   => date('Y-m-d H:i:s'),
        ));
    }
}
