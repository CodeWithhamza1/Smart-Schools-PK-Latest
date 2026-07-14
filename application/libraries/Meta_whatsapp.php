<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meta_whatsapp {

    private $access_token;
    private $phone_number_id;

    public function __construct($params)
    {
        $this->access_token = $params['access_token'];
        $this->phone_number_id = $params['phone_number_id'];
    }

    public function sendTemplate($to, $template_name, $language, $components)
    {
        $to = ltrim((string)$to, '+');
        $template_name = trim((string)$template_name);
        $language = trim((string)$language);

        if ($to === '' || $template_name === '') {
            log_message('error', 'Meta WhatsApp send skipped due to missing recipient or template name.');
            return false;
        }

        if ($language === '') {
            $language = 'en_US';
        }

        $url = "https://graph.facebook.com/v19.0/{$this->phone_number_id}/messages";

        $postData = [
            "messaging_product" => "whatsapp",
            "to" => $to,
            "type" => "template",
            "template" => [
                "name" => $template_name,
                "language" => ["code" => $language],
                "components" => $components
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->access_token}",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($postData)
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            log_message('error', 'Meta WhatsApp curl error: ' . $error);
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        return $response;
    }
}
