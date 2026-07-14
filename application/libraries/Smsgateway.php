<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Smsgateway {

    private $_CI;
    private $sch_setting;

    public function __construct() {
        $this->_CI = &get_instance();
        $this->_CI->load->model('setting_model');
        $this->_CI->load->model('student_model');
        $this->_CI->load->model('teacher_model');
        $this->_CI->load->model('studentfeemaster_model');
        $this->_CI->load->model('librarian_model');
        $this->_CI->load->model('accountant_model');
        $this->_CI->load->model('smsconfig_model');
        $this->sch_setting = $this->_CI->setting_model->get();
    }

    public function sentNotification($send_to, $template = '', $detail) {
        $this->_CI->load->library('pushnotification');
        $msg = $this->getContent($detail, $template);
        $push_array = array(
            'title' => 'Fee Reminder',
            'body' => $msg
        );
        if ($send_to != "") {
            $this->_CI->pushnotification->send($send_to, $push_array, "mail_sms");
        }
    }

    public function sendSMS($send_to, $template = '', $detail) {
        $sms_detail = $this->_CI->smsconfig_model->getActiveSMS();
       
        if ($template != "") {
            $msg = $this->getContent($detail, $template);
        } else {
            $msg = $detail;
        }
        $school = $this->_CI->setting_model->get();
        $schoolname = $school[0]['name'];
        $number = $send_to;
        if (!empty($sms_detail)) {
            if ($sms_detail->type == 'custom') {
                $this->_CI->load->library('customsms');
                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $this->_CI->customsms->sendSMS($to, $message);

            }elseif ($sms_detail->type == 'local_sms_gateway') {

                $this->_CI->load->library('localsms');
                $device = $this->_CI->smsconfig_model->get_device_model();
                $device_id= $device['name'];
                $msg1= $schoolname.' - '.$msg;
                $sim_id = 99;
                $data_type = 'Plain';
                $responce = $this->_CI->localsms->sendSMS($send_to, $msg1, $device_id, $sim_id, $data_type);
            } else {

            }
        }
        return true;
    }
    public function sendSMSUrdu($send_to, $template = '', $detail) {
        $sms_detail = $this->_CI->smsconfig_model->getActiveSMS();
        if ($template != "") {
            $msg = $this->getContent($detail, $template);
        } else {
            $msg = $detail;
        }
        $school = $this->_CI->setting_model->get();
        $schoolname = $school[0]['name'];
        $number = $send_to;
        if (!empty($sms_detail)) {
            if ($sms_detail->type == 'custom') {
                $this->_CI->load->library('customsms');
                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $this->_CI->customsms->sendSMSUrdu($to, $message);
            }elseif ($sms_detail->type == 'local_sms_gateway') {

                $this->_CI->load->library('localsms');
                $device = $this->_CI->smsconfig_model->get_device_model();
                $device_id= $device['name'];
                $msg1= $schoolname.' - '.$msg;
                $sim_id = 99;
                $data_type = 'Plain';
                $this->_CI->localsms->sendSMS($send_to, $msg1, $device_id, $sim_id, $data_type);
            } else {

            }
        }

        return true;
    }

    public function sentRegisterSMS($id, $send_to, $template) {
        $sms_detail = $this->_CI->smsconfig_model->getActiveSMS();
        $msg = $this->getStudentRegistrationContent($id, $template);
        /*if (!empty($sms_detail)) {
            if ($sms_detail->type == 'clickatell') {

                $params = array(
                    'apiToken' => $sms_detail->api_id,
                );
                $this->_CI->load->library('clickatell', $params);
                try {
                    $result = $this->_CI->clickatell->sendMessage(['to' => [$send_to], 'content' => $msg]);
                    foreach ($result['messages'] as $message) {

                    }
                    return true;
                } catch (Exception $e) {
                    return true;
                }
            } else if ($sms_detail->type == 'twilio') {

                $params = array(
                    'mode' => 'sandbox',
                    'account_sid' => $sms_detail->api_id,
                    'auth_token' => $sms_detail->password,
                    'api_version' => '2010-04-01',
                    'number' => $sms_detail->contact,
                );

                $this->_CI->load->library('twilio', $params);

                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $response = $this->_CI->twilio->sms($from, $to, $message);

                if ($response->IsError) {
                    return true;
                } else {
                    return true;
                }
            } else if ($sms_detail->type == 'msg_nineone') {
                $params = array(
                    'authkey' => $sms_detail->authkey,
                    'senderid' => $sms_detail->senderid,
                );
                $this->_CI->load->library('msgnineone', $params);
                $this->_CI->msgnineone->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'smscountry') {
                $params = array(
                    'username' => $sms_detail->username,
                    'sernderid' => $sms_detail->senderid,
                    'password' => $sms_detail->password,
                );
                $this->_CI->load->library('smscountry', $params);
                $this->_CI->smscountry->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'text_local') {
                $to = $send_to;
                $params = array(
                    'username' => $sms_detail->username,
                    'hash' => $sms_detail->password,
                );
                $this->_CI->load->library('textlocalsms', $params);
                $this->_CI->textlocalsms->sendSms(array($to), $msg, $sms_detail->senderid);
            } else if ($sms_detail->type == 'custom') {
                $this->_CI->load->library('customsms');
                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $this->_CI->customsms->sendSMS($to, $message);
            } else {

            }
        }*/
        $this->sendSMS($send_to,"", $msg);
        return true;
    }
    public function sentVisitorSMS($name, $send_to) {
        $school =  $this->_CI->setting_model->get();
        $schoolname = $school[0]['name'];
        $msg = "Dear ".$name." Our Family's heartiest welcome goes to you. Thanks for the visit ".$schoolname.".";
        $this->sendSMS($send_to,"", $msg);
    }
    public function sentAdmissionEnquirySMS($name, $send_to) {
    $school =  $this->_CI->setting_model->get();
    $schoolname = $school[0]['name'];
    $msg = "Thank you for visiting Hijaz Schools International(Manawan Campus) Main G.T Road Addah Chhabel. We are welcome you to our school and will be extremely happy to join us.\n03004119652";
    $this->sendSMS($send_to,"", $msg);
    }
    public function sentComplaintSMS($name,$id, $send_to) {

    $msg = "Dear ".$name." Your complaint number is ".$id.". Our Complaint cell team will look over your Complaint. We will respond to you as soon as possible.";
    $this->sendSMS($send_to,"", $msg);
    }
    public function sentComplaintResolveSMS($name,$id, $send_to) {

    $msg = "Dear ".$name." Complaint cell team has been done work on your Complaint which number is ".$id.". Your follow up on the behalf of this will be appreciated.";
    $this->sendSMS($send_to,"", $msg);
    }
    public function sentAddFeeSMS($detail, $template) {

        $sms_detail = $this->_CI->smsconfig_model->getActiveSMS();
        $send_to = $detail->contact_no;
        $msg = $this->getAddFeeContent($detail, $template);

        /*if (!empty($sms_detail)) {
            if ($sms_detail->type == 'clickatell') {

                $params = array(
                    'apiToken' => $sms_detail->api_id,
                );
                $this->_CI->load->library('clickatell', $params);
                try {
                    $result = $this->_CI->clickatell->sendMessage(['to' => [$send_to], 'content' => $msg]);
                    foreach ($result['messages'] as $message) {

                    }
                    return true;
                } catch (Exception $e) {
                    return true;
                }
            } else if ($sms_detail->type == 'twilio') {

                $params = array(
                    'mode' => 'sandbox',
                    'account_sid' => $sms_detail->api_id,
                    'auth_token' => $sms_detail->password,
                    'api_version' => '2010-04-01',
                    'number' => $sms_detail->contact,
                );

                $this->_CI->load->library('twilio', $params);

                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $response = $this->_CI->twilio->sms($from, $to, $message);

                if ($response->IsError) {
                    return true;
                } else {
                    return true;
                }
            } else if ($sms_detail->type == 'msg_nineone') {
                $params = array(
                    'authkey' => $sms_detail->authkey,
                    'senderid' => $sms_detail->senderid,
                );
                $this->_CI->load->library('msgnineone', $params);
                $this->_CI->msgnineone->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'smscountry') {
                $params = array(
                    'username' => $sms_detail->username,
                    'sernderid' => $sms_detail->senderid,
                    'password' => $sms_detail->password,
                );
                $this->_CI->load->library('smscountry', $params);
                $this->_CI->smscountry->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'text_local') {
                $to = $send_to;
                $params = array(
                    'username' => $sms_detail->username,
                    'hash' => $sms_detail->password,
                );
                $this->_CI->load->library('textlocalsms', $params);
                $this->_CI->textlocalsms->sendSms(array($to), $msg, $sms_detail->senderid);
            } else if ($sms_detail->type == 'custom') {
                $this->_CI->load->library('customsms');
                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $this->_CI->customsms->sendSMS($to, $message);
            } else {

            }
        }*/
        $this->sendSMS($send_to,"", $msg);
    }
    public function sentGroupAddFeeSMS($student_session_id,$admission_no,$std_name,$invoice_amount, $send_to) {
        $currency_symbol = $this->sch_setting[0]['currency_symbol'];
        $school_name = $this->sch_setting[0]['name'];
        $balance = $this->getPendingBalance($student_session_id);
        $fee_amount =$invoice_amount;
        $msg = "Fees received for " . $std_name. "(".$admission_no.")". "Paid Fees " . $currency_symbol . $fee_amount. "Unpaid Fees " . $currency_symbol . $balance . "/- Received by " . $school_name;

        $this->sendSMS($send_to, $msg);
    }
    public function sentAbsentStudentSMS($detail, $template) {
      
        $sms_detail = $this->_CI->smsconfig_model->getActiveSMS();

        if (!empty($sms_detail)) {

            $send_to = $detail['guardian_phone'];
            $msg = $this->getAbsentStudentContent($detail, $template);

            /*if (!empty($sms_detail)) {
             if ($sms_detail->type == 'clickatell') {

                 $params = array(
                     'apiToken' => $sms_detail->api_id,
                 );
                 $this->_CI->load->library('clickatell', $params);
                 try {
                     $result = $this->_CI->clickatell->sendMessage(['to' => [$send_to], 'content' => $msg]);
                     foreach ($result['messages'] as $message) {

                     }
                     return true;
                 } catch (Exception $e) {
                     return true;
                 }
             } else if ($sms_detail->type == 'twilio') {

                 $params = array(
                     'mode' => 'sandbox',
                     'account_sid' => $sms_detail->api_id,
                     'auth_token' => $sms_detail->password,
                     'api_version' => '2010-04-01',
                     'number' => $sms_detail->contact,
                 );

                 $this->_CI->load->library('twilio', $params);

                 $from = $sms_detail->contact;
                 $to = $send_to;
                 $message = $msg;
                 $response = $this->_CI->twilio->sms($from, $to, $message);

                 if ($response->IsError) {
                     return true;
                 } else {
                     return true;
                 }
             } else if ($sms_detail->type == 'msg_nineone') {
                 $params = array(
                     'authkey' => $sms_detail->authkey,
                     'senderid' => $sms_detail->senderid,
                 );
                 $this->_CI->load->library('msgnineone', $params);
                 $this->_CI->msgnineone->sendSMS($send_to, $msg);
             } else if ($sms_detail->type == 'smscountry') {
                 $params = array(
                     'username' => $sms_detail->username,
                     'sernderid' => $sms_detail->senderid,
                     'password' => $sms_detail->password,
                 );
                 $this->_CI->load->library('smscountry', $params);
                 $this->_CI->smscountry->sendSMS($send_to, $msg);
             } else if ($sms_detail->type == 'text_local') {
                 $to = $send_to;
                 $params = array(
                     'username' => $sms_detail->username,
                     'hash' => $sms_detail->password,
                 );
                 $this->_CI->load->library('textlocalsms', $params);
                 $this->_CI->textlocalsms->sendSms(array($to), $msg, $sms_detail->senderid);
             } else if ($sms_detail->type == 'custom') {
                 $this->_CI->load->library('customsms');
                 $from = $sms_detail->contact;
                 $to = $send_to;
                 $message = $msg;
                 $this->_CI->customsms->sendSMS($to, $message);
             } else {

             }
         }*/
            $this->sendSMS($send_to,"", $msg);
        }
    }

    public function sentAbsentStudentNotification($detail, $template) {

        $msg = $this->getAbsentStudentContent($detail, $template);
        $push_array = array(
            'title' => 'Absent Student',
            'body' => $msg,
        );
        if ($detail['parent_app_key'] != "") {
            $this->_CI->pushnotification->send($detail['parent_app_key'], $push_array, "mail_sms");
        }
    }

    public function sentExamResultNotification($detail, $template) {

        $msg = $this->getStudentResultContent($detail, $template);
        $push_array = array(
            'title' => 'Exam Result',
            'body' => $msg,
        );
        if ($detail['app_key'] != "") {
            $this->_CI->pushnotification->send($detail['app_key'], $push_array, "mail_sms");
        }
        if ($detail['parent_app_key'] != "") {
            $this->_CI->pushnotification->send($detail['parent_app_key'], $push_array, "mail_sms");
        }
    }

    public function sentExamResultSMS($detail, $template) {

        $sms_detail = $this->_CI->smsconfig_model->getActiveSMS();
        $msg = $this->getStudentResultContent($detail, $template);

        $send_to = $detail['guardian_phone'];

        /*if (!empty($sms_detail)) {
                    if ($sms_detail->type == 'clickatell') {

                        $params = array(
                            'apiToken' => $sms_detail->api_id,
                        );
                        $this->_CI->load->library('clickatell', $params);
                        try {
                            $result = $this->_CI->clickatell->sendMessage(['to' => [$send_to], 'content' => $msg]);
                            foreach ($result['messages'] as $message) {

                            }
                            return true;
                        } catch (Exception $e) {
                            return true;
                        }
                    } else if ($sms_detail->type == 'twilio') {

                        $params = array(
                            'mode' => 'sandbox',
                            'account_sid' => $sms_detail->api_id,
                            'auth_token' => $sms_detail->password,
                            'api_version' => '2010-04-01',
                            'number' => $sms_detail->contact,
                        );

                        $this->_CI->load->library('twilio', $params);

                        $from = $sms_detail->contact;
                        $to = $send_to;
                        $message = $msg;
                        $response = $this->_CI->twilio->sms($from, $to, $message);

                        if ($response->IsError) {
                            return true;
                        } else {
                            return true;
                        }
                    } else if ($sms_detail->type == 'msg_nineone') {
                        $params = array(
                            'authkey' => $sms_detail->authkey,
                            'senderid' => $sms_detail->senderid,
                        );
                        $this->_CI->load->library('msgnineone', $params);
                        $this->_CI->msgnineone->sendSMS($send_to, $msg);
                    } else if ($sms_detail->type == 'smscountry') {
                        $params = array(
                            'username' => $sms_detail->username,
                            'sernderid' => $sms_detail->senderid,
                            'password' => $sms_detail->password,
                        );
                        $this->_CI->load->library('smscountry', $params);
                        $this->_CI->smscountry->sendSMS($send_to, $msg);
                    } else if ($sms_detail->type == 'text_local') {
                        $to = $send_to;
                        $params = array(
                            'username' => $sms_detail->username,
                            'hash' => $sms_detail->password,
                        );
                        $this->_CI->load->library('textlocalsms', $params);
                        $this->_CI->textlocalsms->sendSms(array($to), $msg, $sms_detail->senderid);
                    } else if ($sms_detail->type == 'custom') {
                        $this->_CI->load->library('customsms');
                        $from = $sms_detail->contact;
                        $to = $send_to;
                        $message = $msg;
                        $this->_CI->customsms->sendSMS($to, $message);
                    } else {

                    }
                }*/
        $this->sendSMS($send_to,"", $msg);        return true;
    }

    public function sendLoginCredential($chk_mail_sms, $sender_details, $template) {
        $sms_detail = $this->_CI->smsconfig_model->getActiveSMS();
        $msg = $this->getLoginCredentialContent($sender_details['credential_for'], $sender_details, $template);

        $send_to = $sender_details['contact_no'];
        /*if (!empty($sms_detail)) {
                    if ($sms_detail->type == 'clickatell') {

                        $params = array(
                            'apiToken' => $sms_detail->api_id,
                        );
                        $this->_CI->load->library('clickatell', $params);
                        try {
                            $result = $this->_CI->clickatell->sendMessage(['to' => [$send_to], 'content' => $msg]);
                            foreach ($result['messages'] as $message) {

                            }
                            return true;
                        } catch (Exception $e) {
                            return true;
                        }
                    } else if ($sms_detail->type == 'twilio') {

                        $params = array(
                            'mode' => 'sandbox',
                            'account_sid' => $sms_detail->api_id,
                            'auth_token' => $sms_detail->password,
                            'api_version' => '2010-04-01',
                            'number' => $sms_detail->contact,
                        );

                        $this->_CI->load->library('twilio', $params);

                        $from = $sms_detail->contact;
                        $to = $send_to;
                        $message = $msg;
                        $response = $this->_CI->twilio->sms($from, $to, $message);

                        if ($response->IsError) {
                            return true;
                        } else {
                            return true;
                        }
                    } else if ($sms_detail->type == 'msg_nineone') {
                        $params = array(
                            'authkey' => $sms_detail->authkey,
                            'senderid' => $sms_detail->senderid,
                        );
                        $this->_CI->load->library('msgnineone', $params);
                        $this->_CI->msgnineone->sendSMS($send_to, $msg);
                    } else if ($sms_detail->type == 'smscountry') {
                        $params = array(
                            'username' => $sms_detail->username,
                            'sernderid' => $sms_detail->senderid,
                            'password' => $sms_detail->password,
                        );
                        $this->_CI->load->library('smscountry', $params);
                        $this->_CI->smscountry->sendSMS($send_to, $msg);
                    } else if ($sms_detail->type == 'text_local') {
                        $to = $send_to;
                        $params = array(
                            'username' => $sms_detail->username,
                            'hash' => $sms_detail->password,
                        );
                        $this->_CI->load->library('textlocalsms', $params);
                        $this->_CI->textlocalsms->sendSms(array($to), $msg, $sms_detail->senderid);
                    } else if ($sms_detail->type == 'custom') {
                        $this->_CI->load->library('customsms');
                        $from = $sms_detail->contact;
                        $to = $send_to;
                        $message = $msg;
                        $this->_CI->customsms->sendSMS($to, $message);
                    } else {

                    }
                }*/
        $this->sendSMS($send_to,"", $msg);        return true;
    }

    public function sentHomeworkStudentNotification($detail, $template) {
        foreach ($detail as $student_key => $student_value) {
            $msg = $this->getHomeworkStudentContent($detail[$student_key], $template);
            $push_array = array(
                'title' => 'Homework',
                'body' => $msg
            );
            if ($student_value['app_key'] != "") {
                $this->_CI->pushnotification->send($student_value['app_key'], $push_array, "mail_sms");
            }
        }
    }

     public function sentOnlineexamStudentNotification($detail, $template) {
        foreach ($detail as $student_key => $student_value) {
            $msg = $this->getOnlineexamStudentContent($detail[$student_key], $template);
            $push_array = array(
                'title' => 'Online Examination',
                'body' => $msg
            );
            if ($student_value['app_key'] != "") {
                $this->_CI->pushnotification->send($student_value['app_key'], $push_array, "mail_sms");
            }
        }
    }

    public function sentOnlineClassStudentNotification($detail, $template) {
        foreach ($detail as $student_key => $student_value) {
            $msg = $this->getOnlineClassStudentContent($detail[$student_key], $template);

            $push_array = array(
                'title' => 'Online Class',
                'body' => $msg
            );

            if ($student_value['app_key'] != "") {
                $this->_CI->pushnotification->send($student_value['app_key'], $push_array, "mail_sms");
            }
        }
    }

    public function sentAddFeeNotification($detail, $template) {
        $msg = $this->getAddFeeContent($detail, $template);
        $push_array = array(
            'title' => 'Fees Notification',
            'body' => $msg
        );
        if ($detail->parent_app_key != "") {
            $this->_CI->pushnotification->send($detail->parent_app_key, $push_array, "mail_sms");
        }
    }

    public function sentHomeworkStudentSMS($detail, $template) {
        $sms_detail = $this->_CI->smsconfig_model->getActiveSMS();
        if (!empty($sms_detail)) {

            foreach ($detail as $student_key => $student_value) {
                $send_to = $student_key;
                if ($send_to != "") {
                    $msg = $this->getHomeworkStudentContent($detail[$student_key], $template);
                    $subject = "HomeWork Notice";
                    /*if (!empty($sms_detail)) {
            if ($sms_detail->type == 'clickatell') {

                $params = array(
                    'apiToken' => $sms_detail->api_id,
                );
                $this->_CI->load->library('clickatell', $params);
                try {
                    $result = $this->_CI->clickatell->sendMessage(['to' => [$send_to], 'content' => $msg]);
                    foreach ($result['messages'] as $message) {

                    }
                    return true;
                } catch (Exception $e) {
                    return true;
                }
            } else if ($sms_detail->type == 'twilio') {

                $params = array(
                    'mode' => 'sandbox',
                    'account_sid' => $sms_detail->api_id,
                    'auth_token' => $sms_detail->password,
                    'api_version' => '2010-04-01',
                    'number' => $sms_detail->contact,
                );

                $this->_CI->load->library('twilio', $params);

                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $response = $this->_CI->twilio->sms($from, $to, $message);

                if ($response->IsError) {
                    return true;
                } else {
                    return true;
                }
            } else if ($sms_detail->type == 'msg_nineone') {
                $params = array(
                    'authkey' => $sms_detail->authkey,
                    'senderid' => $sms_detail->senderid,
                );
                $this->_CI->load->library('msgnineone', $params);
                $this->_CI->msgnineone->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'smscountry') {
                $params = array(
                    'username' => $sms_detail->username,
                    'sernderid' => $sms_detail->senderid,
                    'password' => $sms_detail->password,
                );
                $this->_CI->load->library('smscountry', $params);
                $this->_CI->smscountry->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'text_local') {
                $to = $send_to;
                $params = array(
                    'username' => $sms_detail->username,
                    'hash' => $sms_detail->password,
                );
                $this->_CI->load->library('textlocalsms', $params);
                $this->_CI->textlocalsms->sendSms(array($to), $msg, $sms_detail->senderid);
            } else if ($sms_detail->type == 'custom') {
                $this->_CI->load->library('customsms');
                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $this->_CI->customsms->sendSMS($to, $message);
            } else {

            }
        }*/
                    $this->sendSMS($send_to,"", $msg);
                }
            }
        }
    }

     public function sentOnlineexamStudentSMS($detail, $template) {
        $sms_detail = $this->_CI->smsconfig_model->getActiveSMS();
        if (!empty($sms_detail)) {

            foreach ($detail as $student_key => $student_value) {
                $send_to = $student_key;
                if ($send_to != "") {
                    $msg = $this->getOnlineexamStudentContent($detail[$student_key], $template);
                    $subject = "Online Examination Notice";
                    /*if (!empty($sms_detail)) {
            if ($sms_detail->type == 'clickatell') {

                $params = array(
                    'apiToken' => $sms_detail->api_id,
                );
                $this->_CI->load->library('clickatell', $params);
                try {
                    $result = $this->_CI->clickatell->sendMessage(['to' => [$send_to], 'content' => $msg]);
                    foreach ($result['messages'] as $message) {

                    }
                    return true;
                } catch (Exception $e) {
                    return true;
                }
            } else if ($sms_detail->type == 'twilio') {

                $params = array(
                    'mode' => 'sandbox',
                    'account_sid' => $sms_detail->api_id,
                    'auth_token' => $sms_detail->password,
                    'api_version' => '2010-04-01',
                    'number' => $sms_detail->contact,
                );

                $this->_CI->load->library('twilio', $params);

                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $response = $this->_CI->twilio->sms($from, $to, $message);

                if ($response->IsError) {
                    return true;
                } else {
                    return true;
                }
            } else if ($sms_detail->type == 'msg_nineone') {
                $params = array(
                    'authkey' => $sms_detail->authkey,
                    'senderid' => $sms_detail->senderid,
                );
                $this->_CI->load->library('msgnineone', $params);
                $this->_CI->msgnineone->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'smscountry') {
                $params = array(
                    'username' => $sms_detail->username,
                    'sernderid' => $sms_detail->senderid,
                    'password' => $sms_detail->password,
                );
                $this->_CI->load->library('smscountry', $params);
                $this->_CI->smscountry->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'text_local') {
                $to = $send_to;
                $params = array(
                    'username' => $sms_detail->username,
                    'hash' => $sms_detail->password,
                );
                $this->_CI->load->library('textlocalsms', $params);
                $this->_CI->textlocalsms->sendSms(array($to), $msg, $sms_detail->senderid);
            } else if ($sms_detail->type == 'custom') {
                $this->_CI->load->library('customsms');
                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $this->_CI->customsms->sendSMS($to, $message);
            } else {

            }
        }*/
                    $this->sendSMS($send_to,"", $msg);
                }
            }
        }
    }

    public function getStudentRegistrationContent($id, $template) {

        $session_name = $this->_CI->setting_model->getCurrentSessionName();
        $student = $this->_CI->student_model->get($id);
        $student['current_session_name'] = $session_name;

        foreach ($student as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return $template;
    }

    public function getAddFeeContent($data, $template) {
        $currency_symbol = $this->sch_setting[0]['currency_symbol'];
        $school_name = $this->sch_setting[0]['name'];
        $invoice_data = json_decode($data->invoice);
        $data->invoice_id = $invoice_data->invoice_id;
        $data->sub_invoice_id = $invoice_data->sub_invoice_id;
        $fee = $this->_CI->studentfeemaster_model->getFeeByInvoice($data->invoice_id, $data->sub_invoice_id);
        $a = json_decode($fee->amount_detail);
        $record = $a->{$data->sub_invoice_id};
        $fee_amount = number_format((($record->amount + $record->amount_fine)), 2, '.', ',');
        $data->firstname = $fee->firstname;
        $data->lastname = $fee->lastname;
        $data->class = $fee->class;
        $data->section = $fee->section;
        $data->fee_amount = $currency_symbol . $fee_amount;

        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return $template;
    }

    public function sentOnlineClassStudentSMS($detail, $template) {

        $sms_detail = $this->_CI->smsconfig_model->getActiveSMS();
        if (!empty($sms_detail)) {

            foreach ($detail as $student_key => $student_value) {
                $send_to = $student_key;
                if ($send_to != "") {
                    $msg = $this->getOnlineClassStudentContent($detail[$student_key], $template);

                    $subject = "Online Class";
                    /*if (!empty($sms_detail)) {
            if ($sms_detail->type == 'clickatell') {

                $params = array(
                    'apiToken' => $sms_detail->api_id,
                );
                $this->_CI->load->library('clickatell', $params);
                try {
                    $result = $this->_CI->clickatell->sendMessage(['to' => [$send_to], 'content' => $msg]);
                    foreach ($result['messages'] as $message) {

                    }
                    return true;
                } catch (Exception $e) {
                    return true;
                }
            } else if ($sms_detail->type == 'twilio') {

                $params = array(
                    'mode' => 'sandbox',
                    'account_sid' => $sms_detail->api_id,
                    'auth_token' => $sms_detail->password,
                    'api_version' => '2010-04-01',
                    'number' => $sms_detail->contact,
                );

                $this->_CI->load->library('twilio', $params);

                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $response = $this->_CI->twilio->sms($from, $to, $message);

                if ($response->IsError) {
                    return true;
                } else {
                    return true;
                }
            } else if ($sms_detail->type == 'msg_nineone') {
                $params = array(
                    'authkey' => $sms_detail->authkey,
                    'senderid' => $sms_detail->senderid,
                );
                $this->_CI->load->library('msgnineone', $params);
                $this->_CI->msgnineone->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'smscountry') {
                $params = array(
                    'username' => $sms_detail->username,
                    'sernderid' => $sms_detail->senderid,
                    'password' => $sms_detail->password,
                );
                $this->_CI->load->library('smscountry', $params);
                $this->_CI->smscountry->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'text_local') {
                $to = $send_to;
                $params = array(
                    'username' => $sms_detail->username,
                    'hash' => $sms_detail->password,
                );
                $this->_CI->load->library('textlocalsms', $params);
                $this->_CI->textlocalsms->sendSms(array($to), $msg, $sms_detail->senderid);
            } else if ($sms_detail->type == 'custom') {
                $this->_CI->load->library('customsms');
                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $this->_CI->customsms->sendSMS($to, $message);
            } else {

            }
        }*/
                    $this->sendSMS($send_to,"", $msg);
                }
            }
        }
    }

    public function sentOnlineMeetingStaffSMS($detail, $template) {

        $sms_detail = $this->_CI->smsconfig_model->getActiveSMS();
        if (!empty($sms_detail)) {

            foreach ($detail as $staff_key => $staff_value) {
                $send_to = $staff_key;
                if ($send_to != "") {
                    $msg = $this->getOnlineMeetingStaffContent($detail[$staff_key], $template);

                    $subject = "Online Meeting";
                    /*if (!empty($sms_detail)) {
            if ($sms_detail->type == 'clickatell') {

                $params = array(
                    'apiToken' => $sms_detail->api_id,
                );
                $this->_CI->load->library('clickatell', $params);
                try {
                    $result = $this->_CI->clickatell->sendMessage(['to' => [$send_to], 'content' => $msg]);
                    foreach ($result['messages'] as $message) {

                    }
                    return true;
                } catch (Exception $e) {
                    return true;
                }
            } else if ($sms_detail->type == 'twilio') {

                $params = array(
                    'mode' => 'sandbox',
                    'account_sid' => $sms_detail->api_id,
                    'auth_token' => $sms_detail->password,
                    'api_version' => '2010-04-01',
                    'number' => $sms_detail->contact,
                );

                $this->_CI->load->library('twilio', $params);

                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $response = $this->_CI->twilio->sms($from, $to, $message);

                if ($response->IsError) {
                    return true;
                } else {
                    return true;
                }
            } else if ($sms_detail->type == 'msg_nineone') {
                $params = array(
                    'authkey' => $sms_detail->authkey,
                    'senderid' => $sms_detail->senderid,
                );
                $this->_CI->load->library('msgnineone', $params);
                $this->_CI->msgnineone->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'smscountry') {
                $params = array(
                    'username' => $sms_detail->username,
                    'sernderid' => $sms_detail->senderid,
                    'password' => $sms_detail->password,
                );
                $this->_CI->load->library('smscountry', $params);
                $this->_CI->smscountry->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'text_local') {
                $to = $send_to;
                $params = array(
                    'username' => $sms_detail->username,
                    'hash' => $sms_detail->password,
                );
                $this->_CI->load->library('textlocalsms', $params);
                $this->_CI->textlocalsms->sendSms(array($to), $msg, $sms_detail->senderid);
            } else if ($sms_detail->type == 'custom') {
                $this->_CI->load->library('customsms');
                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $this->_CI->customsms->sendSMS($to, $message);
            } else {

            }
        }*/
                    $this->sendSMS($send_to,"", $msg);
                }
            }
        }
    }

    public function getLoginCredentialContent($credential_for, $sender_details, $template) {
        if ($credential_for == "student") {
            $student = $this->_CI->student_model->get($sender_details['id']);
            $sender_details['url'] = site_url('site/userlogin');
            $sender_details['display_name'] = $student['firstname'] . " " . $student['lastname'];
        } elseif ($credential_for == "parent") {
            $parent = $this->_CI->student_model->get($sender_details['id']);
            $sender_details['url'] = site_url('site/userlogin');
            $sender_details['display_name'] = $parent['guardian_name'];
        } elseif ($credential_for == "staff") {
            $staff = $this->_CI->staff_model->get($sender_details['id']);
            $sender_details['url'] = site_url('site/login');
            $sender_details['display_name'] = $staff['name'];
        }

        foreach ($sender_details as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return $template;
    }

    public function getAbsentStudentContent($student_detail, $template) {

        $session_name = $this->_CI->setting_model->getCurrentSessionName();
        $student_detail['current_session_name'] = $session_name;
        foreach ($student_detail as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }

    public function getStudentResultContent($student_result_detail, $template) {

        foreach ($student_result_detail as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }

    public function getContent($sender_details, $template) {

        foreach ($sender_details as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }

        return $template;
    }

    public function getHomeworkStudentContent($student_detail, $template) {

        foreach ($student_detail as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }
      public function getOnlineexamStudentContent($student_detail, $template) {

        foreach ($student_detail as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }

    public function getOnlineClassStudentContent($student_detail, $template) {

        foreach ($student_detail as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }

    public function getOnlineMeetingStaffContent($student_detail, $template) {

        foreach ($student_detail as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }

    public function sentSMSToAlumni($sender_details) {

        $sms_detail = $this->_CI->smsconfig_model->getActiveSMS();
        $msg = $sender_details['subject'] . " - Event From " . $sender_details['from_date'] . " To " . $sender_details['to_date'] . "\n" .
                $sender_details['body'];
        $send_to = $sender_details['contact_no'];

        /*if (!empty($sms_detail)) {
            if ($sms_detail->type == 'clickatell') {

                $params = array(
                    'apiToken' => $sms_detail->api_id,
                );
                $this->_CI->load->library('clickatell', $params);
                try {
                    $result = $this->_CI->clickatell->sendMessage(['to' => [$send_to], 'content' => $msg]);
                    foreach ($result['messages'] as $message) {

                    }
                    return true;
                } catch (Exception $e) {
                    return true;
                }
            } else if ($sms_detail->type == 'twilio') {

                $params = array(
                    'mode' => 'sandbox',
                    'account_sid' => $sms_detail->api_id,
                    'auth_token' => $sms_detail->password,
                    'api_version' => '2010-04-01',
                    'number' => $sms_detail->contact,
                );

                $this->_CI->load->library('twilio', $params);

                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $response = $this->_CI->twilio->sms($from, $to, $message);

                if ($response->IsError) {
                    return true;
                } else {
                    return true;
                }
            } else if ($sms_detail->type == 'msg_nineone') {
                $params = array(
                    'authkey' => $sms_detail->authkey,
                    'senderid' => $sms_detail->senderid,
                );
                $this->_CI->load->library('msgnineone', $params);
                $this->_CI->msgnineone->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'smscountry') {
                $params = array(
                    'username' => $sms_detail->username,
                    'sernderid' => $sms_detail->senderid,
                    'password' => $sms_detail->password,
                );
                $this->_CI->load->library('smscountry', $params);
                $this->_CI->smscountry->sendSMS($send_to, $msg);
            } else if ($sms_detail->type == 'text_local') {
                $to = $send_to;
                $params = array(
                    'username' => $sms_detail->username,
                    'hash' => $sms_detail->password,
                );
                $this->_CI->load->library('textlocalsms', $params);
                $this->_CI->textlocalsms->sendSms(array($to), $msg, $sms_detail->senderid);
            } else if ($sms_detail->type == 'custom') {
                $this->_CI->load->library('customsms');
                $from = $sms_detail->contact;
                $to = $send_to;
                $message = $msg;
                $this->_CI->customsms->sendSMS($to, $message);
            } else {

            }
        }*/
        $this->sendSMS($send_to,"", $msg);
        return true;
    }
    public function getPendingBalance($student_session_id) {
        $date=date('Y-m-d');
        $month=date('m',strtotime($date));
        $student_total_fees = $this->_CI->studentfeemaster_model->getStudentAllFees($student_session_id,$month);
        if (!empty($student_total_fees)) {
            $monthfee = 0;
            $fine = 0;
            $deposit = 0;
            $discount = 0;
            foreach ($student_total_fees as $student_total_fees_key => $student_total_fees_value) {
                if (!empty($student_total_fees_value->fees)) {
                    foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {

                        $amount_detail = json_decode($each_fee_value->amount_detail);
                        $monthfee += $each_fee_value->amount;
                        if (is_object($amount_detail)) {
                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                $deposit += $amount_detail_value->amount;
                                $fine = $fine + $amount_detail_value->amount_fine;
                                $discount = $discount + $amount_detail_value->amount_discount;
                            }
                        }
                    }
                }
            }
            $balance = $monthfee - ($deposit + $discount);
        }
//        print_r($balance);
        return $balance;

    }

}
