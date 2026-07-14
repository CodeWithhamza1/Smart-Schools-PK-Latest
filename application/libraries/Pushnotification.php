<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Pushnotification {
    public $CI;
    private $serviceAccountPath;
    private $projectId = 'unique-school-shama-chap';
    private $fcmUrl;
    private $accessToken;
    
    public function __construct() {
        $this->CI = &get_instance();
        $this->serviceAccountPath = APPPATH . 'config/firebase-service-account.json';
        $this->fcmUrl = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
    }
    
    /**
     * Get OAuth 2.0 access token using service account
     */
    private function getAccessToken() {
        if ($this->accessToken) {
            return $this->accessToken;
        }
        
        if (!file_exists($this->serviceAccountPath)) {
            log_message('error', 'Firebase service account file not found at: ' . $this->serviceAccountPath);
            return false;
        }
        
        $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);
        
        if (!$serviceAccount || !isset($serviceAccount['private_key']) || !isset($serviceAccount['client_email'])) {
            log_message('error', 'Invalid Firebase service account JSON file');
            return false;
        }
        
        // JWT Header
        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];
        
        // JWT Payload
        $now = time();
        $payload = [
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600
        ];
        
        // Create JWT token
        $jwt = $this->createJWT($header, $payload, $serviceAccount['private_key']);
        
        if (!$jwt) {
            log_message('error', 'Failed to create JWT token for Firebase authentication');
            return false;
        }
        
        // Request access token from Google OAuth
        $tokenData = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            log_message('error', 'CURL error getting Firebase access token: ' . $curlError);
            return false;
        }
        
        if ($httpCode == 200) {
            $tokenResponse = json_decode($response, true);
            if (isset($tokenResponse['access_token'])) {
                $this->accessToken = $tokenResponse['access_token'];
                return $this->accessToken;
            }
        }
        
        log_message('error', 'Failed to get Firebase access token. HTTP: ' . $httpCode . ', Response: ' . $response);
        return false;
    }
    

    private function createJWT($header, $payload, $privateKey) {
        try {
            $headerEncoded = $this->base64urlEncode(json_encode($header));
            $payloadEncoded = $this->base64urlEncode(json_encode($payload));
            
            $signature = '';
            $success = openssl_sign(
                $headerEncoded . '.' . $payloadEncoded,
                $signature,
                $privateKey,
                OPENSSL_ALGO_SHA256
            );
            
            if (!$success) {
                log_message('error', 'Failed to sign JWT token with private key');
                return false;
            }
            
            $signatureEncoded = $this->base64urlEncode($signature);
            
            return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
        } catch (Exception $e) {
            log_message('error', 'JWT creation error: ' . $e->getMessage());
            return false;
        }
    }
    

    private function base64urlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    

    public function send($tokens, $msg, $action = "") {
        // Get access token for authentication
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            log_message('error', 'Unable to get Firebase access token for push notification');
            return false;
        }
        
        // Prepare notification data (keeping your original structure)
        $notificationData = [
            'title' => $msg['title'],
            'body' => $msg['body'],
            'action' => $action,
            'sound' => 'default' // Changed from 'mySound' to 'default' for better compatibility
        ];
        
        // Build the FCM message in v1 API format
        $message = [
            'message' => [
                'token' => $tokens, // Single token (same as your 'to' field)
                'notification' => [
                    'title' => $msg['title'],
                    'body' => $msg['body']
                ],
                'data' => [
                    'title' => $msg['title'],
                    'body' => $msg['body'],
                    'action' => $action,
                    'sound' => 'default',
                    'click_action' => $action
                ],
                // Android specific configuration
                'android' => [
                    'notification' => [
                        'title' => $msg['title'],
                        'body' => $msg['body'],
                        'sound' => 'default',
                        'click_action' => $action,
                        'channel_id' => 'default'
                    ],
                    'collapse_key' => $tokens, // Same as your collapseKey
                    'data' => $notificationData
                ],
                // iOS specific configuration
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'alert' => [
                                'title' => $msg['title'],
                                'body' => $msg['body']
                            ],
                            'sound' => 'default'
                        ]
                    ]
                ]
            ]
        ];
        
        // Set headers with Bearer token (new authentication method)
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];
        
        // Send the notification via cURL (same as before, but with new URL and headers)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Handle errors and return results
        if ($curlError) {
            log_message('error', 'CURL error sending FCM notification: ' . $curlError);
            return false;
        }
        
        if ($httpCode == 200) {
            $response = json_decode($result, true);
            if (isset($response['error'])) {
                log_message('error', 'FCM API Error: ' . json_encode($response['error']));
                return false;
            }
            
            // Success - log it
            log_message('info', 'Push notification sent successfully to token: ' . substr($tokens, 0, 20) . '...');
            return true;
        } else {
            log_message('error', 'FCM HTTP Error: ' . $httpCode . ' - Response: ' . $result);
            return false;
        }
    }
    

    public function sendBulk($tokenArray, $msg, $action = "") {
        if (!is_array($tokenArray) || empty($tokenArray)) {
            log_message('error', 'sendBulk requires an array of tokens');
            return false;
        }
        
        $results = [];
        $successCount = 0;
        
        foreach ($tokenArray as $token) {
            $result = $this->send($token, $msg, $action);
            $results[] = [
                'token' => substr($token, 0, 20) . '...',
                'success' => $result
            ];
            
            if ($result) {
                $successCount++;
            }
            
            // Small delay to prevent rate limiting
            usleep(100000); // 0.1 second pause
        }
        
        log_message('info', "Bulk notification completed. Success: $successCount/" . count($tokenArray));
        return [
            'total' => count($tokenArray),
            'success' => $successCount,
            'failed' => count($tokenArray) - $successCount,
            'details' => $results
        ];
    }
    
    public function sendToTopic($topic, $msg, $action = "") {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return false;
        }
        
        $message = [
            'message' => [
                'topic' => $topic,
                'notification' => [
                    'title' => $msg['title'],
                    'body' => $msg['body']
                ],
                'data' => [
                    'action' => $action,
                    'click_action' => $action
                ]
            ]
        ];
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLOPT_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode == 200) {
            log_message('info', 'Topic notification sent successfully to: ' . $topic);
            return true;
        } else {
            log_message('error', 'Topic notification failed. HTTP: ' . $httpCode . ' - ' . $result);
            return false;
        }
    }
}