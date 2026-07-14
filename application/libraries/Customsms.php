

<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Customsms {

    private $_CI;
    var $type = "json";
    var $lang = "English";
    var $lang2 = "Urdu";

//        var $id = "rchalliedkhd";
//        var $pass = "school688";
//        var $mask = "ALLIED KHD";

//        var $id = "rchalliedcam";
//        var $pass = "pecerewe";
//        var $mask = "ALLIED AAC";

//        var $id = "rchsmhskk";
//        var $pass = "school@68";
//        var $mask = "SMHS-KK";

//        var $id = "rchnisschool";
//        var $pass = "school688";
//        var $mask = "NIS-SCHOOL";

//        var $id = "Rchalumaracd";
//        var $pass = "college987";
//        var $mask = "AL UMAR ACD";

//        var $id = "Rchalumarclg";
//        var $pass = "college987";
//        var $mask = "AL UMAR CLG";

//        var $id = "Rchalumarsch";
//        var $pass = "college987";
//        var $mask = "AL UMAR SCH";

//        var $id = "rchpunjabs";
//        var $pass = "school123";
//        var $mask = "PSS-TajBagh";

//        var $id = "pmhs";
//        var $pass = "Yahafizoo786";
//        var $mask = "PMHS-KASUR";
    function __construct() {
        $this->_CI = & get_instance();
        $this->session_name = $this->_CI->setting_model->getCurrentSessionName();
        $this->_CI->load->model('smsconfig_model');
        $detail = $this->_CI->smsconfig_model->get_branded_sms();
        $this->id = $detail['username'];
        $this->pass = $detail['password'];
        $this->mask = $detail['name'];

       /* $this->id = "hall1245";
        $this->pass = "thanet4455";
        $this->mask = "SMS4CONNECT";*/

    }

    function sendSMS($to, $message) {
        /*$content = 'AUTH_KEY=' . rawurlencode($this->AUTH_KEY) .
                '&message=' . rawurlencode($message) .
                '&senderId=' . rawurlencode($this->senderId) .
                '&routeId=' . rawurlencode($this->routeId) .
                '&mobileNos=' . rawurlencode($to) .
                '&smsContentType=' . rawurlencode($this->smsContentType);
        $ch = curl_init('https://yourapiurl.com' . $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);*/

        $message=urlencode($message);
        //   // Prepare data for POST request
        $data234 = "id=" . $this->id . "&pass=" . $this->pass . "&msg=" . $message . "&to=" . $to . "&lang=" . $this->lang . "&mask=" . $this->mask . "&type=" . $this->type;
        
        //   // Send the POST request with cURL
        //$ch = curl_init('http://www.brandedsmsportal.com/API/api-d.php');
        // $ch = curl_init('http://www.outreach.pk/api/sendsms.php/sendsms/url');
       $ch = curl_init('http://www.sms4connect.com/api/sendsms.php/sendsms/url');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data234);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        //This is the result from Outreach
        curl_close($ch);

        //for public css school & km
        /*$ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, "http://api.bizsms.pk/api-send-branded-sms.aspx?username=" . $this->id . "&pass=" . $this->pass . "&text=" . $message . "&masking=" . $this->mask . "&destinationnum=" . $to . "&language=". $this->lang);
        curl_setopt($ch, CURLOPT_URL, "https://panel.prosms.pk/api/quick/message?user=" . $this->id . "&password=" . $this->pass . "&mask=" . $this->mask . "&to=" . $to .  "&message=" . $message);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response =curl_exec($ch);
        curl_close($ch);*/

        return $response;
    }
    function sendSMSUrdu($to, $message) {

        $message=urlencode($message);
        //   // Prepare data for POST request
        $data234 = "id=" . $this->id . "&pass=" . $this->pass . "&msg=" . $message . "&to=" . $to . "&lang=" . $this->lang2 . "&mask=" . $this->mask . "&type=" . $this->type;
        //   // Send the POST request with cURL
        //$ch = curl_init('http://www.brandedsmsportal.com/API/api-d.php');
        $ch = curl_init('http://www.outreach.pk/api/sendsms.php/sendsms/url');
//        $ch = curl_init('http://www.sms4connect.com/api/sendsms.php/sendsms/url');

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data234);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        //This is the result from Outreach
        curl_close($ch);

        //for public css school & km ALERTS
        /*$ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, "http://api.bizsms.pk/api-send-branded-sms.aspx?username=" . $this->id . "&pass=" . $this->pass . "&text=" . $message . "&masking=" . $this->mask . "&destinationnum=" . $to . "&language=". $this->lang);
        curl_setopt($ch, CURLOPT_URL, "https://panel.prosms.pk/api/quick/message?user=" . $this->id . "&password=" . $this->pass . "&mask=" . $this->mask . "&to=" . $to .  "&message=" . $message);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response =curl_exec($ch);
        curl_close($ch);*/

        return $response;
    }

}
?>