<?php



if (!defined('BASEPATH'))

    exit('No direct script access allowed');



class FamilyChallanModel extends CI_Model

{

    public function __construct()

    {
        parent::__construct();

        $this->current_session = $this->setting_model->getCurrentSession();

        $this->current_date = $this->setting_model->getDateYmd();

    }

    public function searchByClassSection($class_id = null, $section_id = null)

    {
        $this->db->select("students.id,CONCAT(firstname,' ',lastname) as student_name,students.admission_no")
            ->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'student_session.class_id = classes.id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('students.is_active', "yes");
        if ($class_id != null) {

            $this->db->where('student_session.class_id', $class_id);

        }
        if ($section_id != null) {

            $this->db->where('student_session.section_id', $section_id);

        }
        $this->db->order_by('students.admission_no', 'asc');
        $this->db->group_by('students.admission_no');
        $query = $this->db->get();
        //return ($this->db->last_query());
        return $query->result_array();

    }

    public function getAllStudentsName()

    {
        $this->db->select("students.id,CONCAT(firstname,' ',lastname) as student_name,students.admission_no")
            ->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('students.is_active', "yes");
        $this->db->order_by('students.admission_no', 'asc');
        $this->db->group_by('students.admission_no');
        $query = $this->db->get();
        //return ($this->db->last_query());
        return $query->result_array();

    }

    public function searchByFamily()

    {
        $this->db->select("students.id,CONCAT(firstname,' ',lastname) as student_name,students.admission_no")
            ->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('students.is_active', "yes");
        $this->db->order_by('students.parent_id', 'asc');
        $this->db->group_by('students.parent_id');
//        $this->db->group_by('students.admission_no');
        $query = $this->db->get();
        //return ($this->db->last_query());
        return $query->result_array();

    }

    public function searchByClassSection2($class_id = null, $section_id = null)

    {

        $this->db->select('classes.id AS `class_id`,student_session.id as student_session_id,students.id,classes.class,sections.id AS `section_id`,sections.section,students.id,students.admission_no , students.roll_no,students.admission_date,students.firstname,  students.lastname,students.image,    students.mobileno, students.email ,students.state ,   students.city , students.pincode ,     students.religion,     students.dob ,students.current_address,    students.permanent_address,IFNULL(students.category_id, 0) as `category_id`,IFNULL(categories.category, "") as `category`,students.adhar_no,students.samagra_id,students.bank_account_no,students.bank_name, students.ifsc_code , students.guardian_name , students.guardian_relation,students.guardian_phone,students.guardian_address,students.is_active ,students.created_at ,students.updated_at,students.father_name,students.rte,students.gender')->from('students');

        $this->db->join('student_session', 'student_session.student_id = students.id');

        $this->db->join('classes', 'student_session.class_id = classes.id');

        $this->db->join('sections', 'sections.id = student_session.section_id');

        $this->db->join('categories', 'students.category_id = categories.id', 'left');

        $this->db->where('student_session.session_id', $this->current_session);

        $this->db->where('students.is_active', "yes");

        if ($class_id != null) {

            $this->db->where('student_session.class_id', $class_id);

        }

        if ($section_id != null) {

            $this->db->where('student_session.section_id', $section_id);

        }

        //$this->db->order_by('students.id');

        $this->db->order_by('students.admission_no', 'asc');



        $query = $this->db->get();



        return $query->result_array();

    }

    public function getDueStudentFees($feegroup_id = null, $fee_groups_feetype_id = null, $class_id = null, $section_id = null)

    {

        $query = "SELECT IFNULL(student_fees_deposite.id, 0) as student_fees_deposite_id, 

IFNULL(student_fees_deposite.fee_groups_feetype_id, 0) as fee_groups_feetype_id, 

IFNULL(student_fees_deposite.amount_detail, 0) as amount_detail, student_fees_master.id 

as `fee_master_id`,fee_groups_feetype.feetype_id ,fee_groups_feetype.amount,fee_groups_feetype.due_date, `classes`.`id` 

AS `class_id`, `student_session`.`id` as `student_session_id`, `students`.`id`, `classes`.`class`, `sections`.`id` 

AS `section_id`, `sections`.`section`, `students`.`id`, `students`.`admission_no`, `students`.`roll_no`,

 `students`.`admission_date`, `students`.`firstname`, `students`.`lastname`, `students`.`image`, `students`.`mobileno`, 

 `students`.`email`, `students`.`state`, `students`.`city`, `students`.`pincode`, `students`.`religion`, `students`.`dob`, 

 `students`.`current_address`, `students`.`permanent_address`, IFNULL(students.category_id, 0) as `category_id`, 

 IFNULL(categories.category, '') as `category`, `students`.`adhar_no`, `students`.`samagra_id`, `students`.`bank_account_no`, 

 `students`.`bank_name`, `students`.`ifsc_code`, `students`.`guardian_name`, `students`.`guardian_relation`, 

 `students`.`guardian_phone`, `students`.`guardian_address`, `students`.`is_active`, `students`.`created_at`, 

 `students`.`updated_at`, `students`.`father_name`, `students`.`rte`, `students`.`gender` FROM `students` 

 JOIN `student_session` ON `student_session`.`student_id` = `students`.`id` JOIN `classes` 

 ON `student_session`.`class_id` = `classes`.`id` JOIN `sections` ON `sections`.`id` = `student_session`.`section_id` 

 LEFT JOIN `categories` ON `students`.`category_id` = `categories`.`id` INNER JOIN student_fees_master on 

 student_fees_master.student_session_id=student_session.id and 

 student_fees_master.fee_session_group_id=" . $this->db->escape($feegroup_id) . " 

 LEFT JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id 

 and student_fees_deposite.fee_groups_feetype_id=" . $this->db->escape($fee_groups_feetype_id) . "  

 INNER JOIN fee_groups_feetype on fee_groups_feetype.id = " . $this->db->escape($fee_groups_feetype_id) . " 

 WHERE `student_session`.`session_id` = " . $this->current_session . " AND 

            `students`.`is_active` = 'yes'  AND 

            `student_session`.`class_id` = " . $this->db->escape($class_id) . " 

            AND `student_session`.`section_id` = " . $this->db->escape($section_id) . " ORDER BY `students`.`id`";

        $query = $this->db->query($query);

        return $query->result_array();

    }

    public function getStudentFees($student_session_id,$month,$year)

    {
        $sql = "SELECT `student_fees_master`.*,fee_groups.name FROM `student_fees_master` 

INNER JOIN fee_session_groups on student_fees_master.fee_session_group_id=fee_session_groups.id 

INNER JOIN fee_groups on fee_groups.id=fee_session_groups.fee_groups_id  

WHERE `student_session_id` = " . $student_session_id . " 

 ORDER BY `student_fees_master`.`id`";

        $query = $this->db->query($sql);

        $result = $query->result();

        if (!empty($result)) {

            foreach ($result as $result_key => $result_value) {

                $fee_session_group_id = $result_value->fee_session_group_id;

                $student_fees_master_id = $result_value->id;

                $result_value->fees = $this->getDueFeeByFeeSessionGroup($fee_session_group_id, $student_fees_master_id,$month,$year);

                if ($result_value->is_system != 0) {

                    $result_value->fees[0]->amount = $result_value->amount;

                }

            }

        }

        return $result;

    }

    public function getDueFeeByFeeSessionGroup($fee_session_groups_id, $student_fees_master_id,$month,$year) {

        $sql = "SELECT student_fees_master.*,fee_groups_feetype.id as `fee_groups_feetype_id`,fee_groups_feetype.amount,fee_groups_feetype.due_date,fee_groups_feetype.fee_groups_id,fee_groups.name,fee_groups_feetype.feetype_id,feetype.code,feetype.type, IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`, 
IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail` FROM `student_fees_master` 
INNER JOIN fee_session_groups on fee_session_groups.id = student_fees_master.fee_session_group_id 
INNER JOIN fee_groups_feetype on  fee_groups_feetype.fee_session_group_id = fee_session_groups.id 
 INNER JOIN fee_groups on fee_groups.id=fee_groups_feetype.fee_groups_id 
 INNER JOIN feetype on feetype.id=fee_groups_feetype.feetype_id 
  INNER JOIN student_session on  student_fees_master.student_session_id = student_session.id
        INNER JOIN students on  student_session.student_id = students.id
 LEFT JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id and student_fees_deposite.fee_groups_feetype_id=fee_groups_feetype.id 
 WHERE student_fees_master.fee_session_group_id =" . $fee_session_groups_id . "
   -- and fee_groups_feetype.due_date >= students.admission_date
 and student_fees_master.id=" . $student_fees_master_id . " 
 and MONTH(fee_groups_feetype.due_date)=". $month ." 
 and YEAR(fee_groups_feetype.due_date)=". $year ." 
 order by fee_groups_feetype.due_date asc";
        $query = $this->db->query($sql);
        //return $this->db->last_query();
        return $query->result();

    }

    public function getStudentPreviousFees($student_session_id,$month,$year)

    {

        $sql = "SELECT `student_fees_master`.*,fee_groups.name FROM `student_fees_master` 
              INNER JOIN fee_session_groups on student_fees_master.fee_session_group_id=fee_session_groups.id 
              INNER JOIN fee_groups on fee_groups.id=fee_session_groups.fee_groups_id  
              WHERE `student_session_id` = " . $student_session_id . " 
                ORDER BY `student_fees_master`.`id`";

        $query = $this->db->query($sql);

        $result = $query->result();

        if (!empty($result)) {

            foreach ($result as $result_key => $result_value) {

                $fee_session_group_id = $result_value->fee_session_group_id;

                $student_fees_master_id = $result_value->id;

                $result_value->fees = $this->getPendingFeeByFeeSessionGroup($fee_session_group_id, $student_fees_master_id,$month,$year,$student_session_id);
                if ($result_value->is_system != 0) {
                    $result_value->fees[0]->amount = $result_value->amount;
                }
            }
        }

        return $result;

    }

    public function getPendingFeeByFeeSessionGroup($fee_session_groups_id, $student_fees_master_id,$month,$year,$student_session_id) {
        $current_date=date('Y-m-d');
        $date = $year.'-'.$month.'-'.'01';
        // $month = date("m",strtotime($current_date));
        /*        $sql = "SELECT student_fees_master.*,fee_groups_feetype.id as `fee_groups_feetype_id`,
                        fee_groups_feetype.amount,fee_groups_feetype.due_date,fee_groups_feetype.fee_groups_id,fee_groups.name,
                        fee_groups_feetype.feetype_id,feetype.code,feetype.type,fee_groups_feetype.due_date
                        FROM `student_fees_master`
                        INNER JOIN fee_session_groups on fee_session_groups.id = student_fees_master.fee_session_group_id
                        INNER JOIN fee_groups_feetype on  fee_groups_feetype.fee_session_group_id = fee_session_groups.id
                        INNER JOIN fee_groups on fee_groups.id=fee_groups_feetype.fee_groups_id
                        INNER JOIN feetype on feetype.id=fee_groups_feetype.feetype_id
                         INNER JOIN student_session on  student_fees_master.student_session_id = student_session.id
                      INNER JOIN students on  student_session.student_id = students.id
                        WHERE student_fees_master.fee_session_group_id ='$fee_session_groups_id'
                        and student_fees_master.id='$student_fees_master_id'
                         and fee_groups_feetype.due_date >= students.admission_date
                       and fee_groups_feetype.id NOT IN (select fee_groups_feetype_id from student_fees_deposite where student_fees_deposite.student_fees_master_id=$student_fees_master_id)
                      -- and MONTH(fee_groups_feetype.due_date)!='$month'
                       and fee_groups_feetype.due_date < '$date'
                       order by fee_groups_feetype.due_date asc";*/

        $sql = "SELECT student_fees_master.*,fee_groups_feetype.id as `fee_groups_feetype_id`,fee_groups_feetype.due_date as `fee_groups_feetype_due_date`,
                fee_groups_feetype.amount,fee_groups_feetype.due_date,fee_groups_feetype.fee_groups_id,
                fee_groups.name,fee_groups_feetype.feetype_id,feetype.code,feetype.type, 
                IFNULL(student_fees_deposite.id,0) as `student_fees_deposite_id`,
                 IFNULL(student_fees_deposite.amount_detail,0) as `amount_detail` ,
                 student_session.class_id,classes.class
                FROM `student_fees_master` 
                INNER JOIN fee_session_groups on fee_session_groups.id = student_fees_master.fee_session_group_id 
                INNER JOIN fee_groups_feetype on  fee_groups_feetype.fee_session_group_id = fee_session_groups.id  
                INNER JOIN fee_groups on fee_groups.id=fee_groups_feetype.fee_groups_id 
                INNER JOIN feetype on feetype.id=fee_groups_feetype.feetype_id 
                 INNER JOIN student_session on  student_fees_master.student_session_id = student_session.id
                 INNER JOIN classes on  student_session.class_id = classes.id
                 INNER JOIN students on  student_session.student_id = students.id
                LEFT JOIN student_fees_deposite on student_fees_deposite.student_fees_master_id=student_fees_master.id 
                and student_fees_deposite.fee_groups_feetype_id=fee_groups_feetype.id 
                WHERE student_fees_master.fee_session_group_id =" . $fee_session_groups_id ." 
                and student_fees_master.id=" . $student_fees_master_id . " 
                 --   and fee_groups_feetype.due_date >= students.admission_date
             -- for sadiq memorial
                and fee_groups_feetype.due_date >= DATE_FORMAT(students.admission_date,'%Y-%m-01')
                -- .........
                -- and fee_groups_feetype.clnd_month < $month
                and fee_groups_feetype.year <= $year
               order by student_session.class_id asc";
        $query =$this->db->query($sql);
        //return $this->db->last_query();
        return $query->result();

    }

}

