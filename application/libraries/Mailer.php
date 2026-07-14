<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Mailer {

    public $mail_config;
    public $last_error = '';
    private $sch_setting;
 
    public function __construct() {
        $this->CI = &get_instance();
        $this->CI->load->model('emailconfig_model');
        $this->CI->mail_config = $this->CI->emailconfig_model->getActiveEmail();
        $this->CI->load->model('setting_model');
        $this->sch_setting = $this->CI->setting_model->get();
    }

    private function normalize_bool($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), array('1', 'true', 'yes', 'on'), true);
    }

    private function configure_common($mail, $timeout)
    {
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = $timeout;
        $mail->SMTPKeepAlive = false;
        $mail->SingleTo = true;
        $mail->WordWrap = 78;
        $mail->isHTML(true);
    }

    private function has_smtp_config()
    {
        return !empty($this->CI->mail_config)
            && $this->CI->mail_config->email_type == "smtp"
            && !empty($this->CI->mail_config->smtp_server)
            && !empty($this->CI->mail_config->smtp_port)
            && !empty($this->CI->mail_config->smtp_username);
    }
 
    private function set_sender($mail, $school_name, $school_email)
    {
        if ($this->CI->mail_config->email_type == "smtp") {
            $mail->SetFrom($this->CI->mail_config->smtp_username, $school_name);
            $mail->AddReplyTo($this->CI->mail_config->smtp_username, $this->CI->mail_config->smtp_username);
        } else {
            $mail->SetFrom($school_email, $school_name);
            $mail->AddReplyTo($school_email, $school_name);
        }
    }

    private function send_with_php_mail($toemail, $subject, $body, $cc, $school_name, $school_email)
    {
        $mail = new PHPMailer();
        $this->configure_common($mail, 30);
        $mail->isMail();
        $mail->SetFrom($school_email, $school_name);
        $mail->AddReplyTo($school_email, $school_name);
        if ($cc != "") {
            $mail->AddCC($cc);
        }
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        $mail->AddAddress($toemail);

        if ($mail->Send()) {
            return true;
        }

        $this->last_error = $mail->ErrorInfo;
        log_message('error', 'PHP mail fallback failed for ' . $toemail . ': ' . $mail->ErrorInfo);
        return false;
    }

    public function send_mail($toemail, $subject, $body, $FILES = array(), $cc = "", $timeout = 30) {

        $this->last_error = '';
        $mail = new PHPMailer();
        $this->configure_common($mail, $timeout);
        $school_name = $this->sch_setting[0]['name'];
        $school_email = $this->sch_setting[0]['email'];
        if (empty($school_email)) {
            $school_email = $this->has_smtp_config() ? $this->CI->mail_config->smtp_username : 'noreply@' . parse_url(base_url(), PHP_URL_HOST);
        }

        if ($this->has_smtp_config()) {

            $mail->IsSMTP();
            $mail->SMTPAuth = $this->normalize_bool($this->CI->mail_config->smtp_auth);
            if (!empty($this->CI->mail_config->ssl_tls)) {
                $mail->SMTPSecure = $this->CI->mail_config->ssl_tls;
            }
            $mail->Host       = $this->CI->mail_config->smtp_server;
            $mail->Port       = $this->CI->mail_config->smtp_port;
            $mail->Username   = $this->CI->mail_config->smtp_username;
            $mail->Password   = $this->CI->mail_config->smtp_password;
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ),
            );
            $this->set_sender($mail, $school_name, $school_email);
        } else {
            return $this->send_with_php_mail($toemail, $subject, $body, $cc, $school_name, $school_email);
        }
        if (!empty($FILES)) {
            if (isset($_FILES['files']) && !empty($_FILES['files'])) {
                $no_files = count($_FILES["files"]['name']);
                for ($i = 0; $i < $no_files; $i++) {
                    if ($_FILES["files"]["error"][$i] > 0) {
                        echo "Error: " . $_FILES["files"]["error"][$i] . "<br>";
                    } else {
                        $file_tmp = $_FILES["files"]["tmp_name"][$i];
                        $file_name = $_FILES["files"]["name"][$i];
                        $mail->AddAttachment($file_tmp, $file_name);
                    }
                }
            }
        }
        if ($cc != "") {

            $mail->AddCC($cc);
        }

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        $mail->AddAddress($toemail);
        if ($mail->Send()) {
            return true;
        } else {
            $this->last_error = $mail->ErrorInfo;
            log_message('error', 'Configured mail delivery failed for ' . $toemail . ': ' . $mail->ErrorInfo);
            return $this->send_with_php_mail($toemail, $subject, $body, $cc, $school_name, $school_email);
        }
    }

}
