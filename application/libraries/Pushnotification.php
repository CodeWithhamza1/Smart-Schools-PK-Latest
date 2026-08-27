<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Pushnotification {
    public $CI;
    private $serviceAccountPath;
    private $projectId = '';
    private $fcmUrl;
    private $accessToken;
    private $serviceAccount;
    
    public function __construct() {
        $this->CI = &get_instance();
        $this->serviceAccountPath = APPPATH . 'config/firebase-service-account.json';
        $this->fcmUrl = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
    }
    
    private function loadServiceAccount() {
        if ($this->serviceAccount) {
            return $this->serviceAccount;
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

        if (!empty($serviceAccount['project_id'])) {
            $this->projectId = $serviceAccount['project_id'];
            $this->fcmUrl = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';
        }

        $this->serviceAccount = $serviceAccount;
        return $this->serviceAccount;
    }

    /**
     * Get OAuth 2.0 access token using service account
     */
    private function getAccessToken() {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $serviceAccount = $this->loadServiceAccount();
        if (!$serviceAccount) {
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

    private function normalizeData($data) {
        $normalized = array();
        foreach ($data as $key => $value) {
            if ($value === null) {
                $value = '';
            } elseif (is_bool($value)) {
                $value = $value ? '1' : '0';
            } elseif (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            $normalized[$key] = (string) $value;
        }
        return $normalized;
    }

    private function clearInvalidToken($token) {
        if ($token === '') {
            return 0;
        }

        $this->CI->db->where('app_key', $token)->update('students', array('app_key' => null));
        $app_key_rows = $this->CI->db->affected_rows();
        $this->CI->db->where('parent_app_key', $token)->update('students', array('parent_app_key' => null));
        $parent_app_key_rows = $this->CI->db->affected_rows();
        $cleared_rows = $app_key_rows + $parent_app_key_rows;
        log_message('info', 'Removed invalid FCM token from students table: ' . substr($token, 0, 20) . '..., rows: ' . $cleared_rows);
        return $cleared_rows;
    }

    private function getFcmErrorCode($response) {
        if (empty($response['error'])) {
            return null;
        }
        if (!empty($response['error']['details']) && is_array($response['error']['details'])) {
            foreach ($response['error']['details'] as $detail) {
                if (!empty($detail['errorCode'])) {
                    return $detail['errorCode'];
                }
            }
        }
        if (!empty($response['error']['status'])) {
            return $response['error']['status'];
        }
        return null;
    }

    private function isInvalidTokenResponse($response) {
        if (empty($response['error'])) {
            return false;
        }

        $error = $response['error'];
        $status = isset($error['status']) ? $error['status'] : '';
        $message = isset($error['message']) ? $error['message'] : '';
        $fcm_error_code = $this->getFcmErrorCode($response);

        if (in_array($status, array('NOT_FOUND', 'UNREGISTERED'), true)
            || in_array($fcm_error_code, array('SENDER_ID_MISMATCH', 'UNREGISTERED', 'NOT_FOUND'), true)) {
            return true;
        }

        // INVALID_ARGUMENT can also describe a malformed message payload. Only
        // clear the saved key when Firebase's message identifies the token.
        return stripos($message, 'registration token') !== false
            || stripos($message, 'not a valid FCM registration token') !== false
            || stripos($message, 'Requested entity was not found') !== false
            || stripos($message, 'SENDER_ID_MISMATCH') !== false
            || stripos($message, 'invalid registration token') !== false;
    }

    public function send($tokens, $msg, $action = "") {
        $report = $this->sendWithReport($tokens, $msg, $action);
        return !empty($report['success']);
    }

    public function sendWithReport($tokens, $msg, $action = "") {
        if (is_array($tokens)) {
            return $this->sendBulk($tokens, $msg, $action);
        }

        $tokens = trim((string) $tokens);
        if ($tokens === '') {
            return array('success' => false, 'driver' => 'http_v1', 'http_code' => 0, 'error' => 'empty_token', 'message' => 'FCM token is empty', 'project_id' => $this->projectId, 'endpoint' => $this->fcmUrl, 'token_prefix' => '', 'response' => null, 'fcm_error_code' => null, 'invalid_token_cleared_rows' => 0);
        }

        // Get access token for authentication
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            log_message('error', 'Unable to get Firebase access token for push notification');
            return array('success' => false, 'driver' => 'http_v1', 'http_code' => 0, 'error' => 'oauth_failed', 'message' => 'Unable to get Firebase access token', 'project_id' => $this->projectId, 'endpoint' => $this->fcmUrl, 'token_prefix' => substr($tokens, 0, 20) . '...', 'response' => null, 'fcm_error_code' => null, 'invalid_token_cleared_rows' => 0);
        }

        $title = isset($msg['title']) ? (string) $msg['title'] : '';
        $body = isset($msg['body']) ? (string) $msg['body'] : '';

        // Prepare notification data (keeping your original structure)
        $notificationData = $this->normalizeData([
            'title' => $title,
            'body' => $body,
            'action' => $action,
            'sound' => 'default' // Changed from 'mySound' to 'default' for better compatibility
        ]);
        
        // Build the FCM message in v1 API format
        $message = [
            'message' => [
                'token' => $tokens, // Single token (same as your 'to' field)
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => $this->normalizeData([
                    'title' => $title,
                    'body' => $body,
                    'action' => $action,
                    'sound' => 'default',
                    'click_action' => $action
                ]),
                // Android specific configuration
                'android' => [
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
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
                                'title' => $title,
                                'body' => $body
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
            return array('success' => false, 'driver' => 'http_v1', 'http_code' => 0, 'error' => 'curl_error', 'message' => $curlError, 'project_id' => $this->projectId, 'endpoint' => $this->fcmUrl, 'token_prefix' => substr($tokens, 0, 20) . '...', 'response' => null, 'fcm_error_code' => null, 'invalid_token_cleared_rows' => 0);
        }

        $response = json_decode($result, true);
        $fcm_error_code = $this->getFcmErrorCode($response);
        if ($httpCode == 200) {
            log_message('info', 'Firebase HTTP v1 push notification sent successfully to token: ' . substr($tokens, 0, 20) . '...');
            return array('success' => true, 'driver' => 'http_v1', 'http_code' => $httpCode, 'project_id' => $this->projectId, 'endpoint' => $this->fcmUrl, 'token_prefix' => substr($tokens, 0, 20) . '...', 'response' => $response, 'fcm_error_code' => null, 'invalid_token_cleared_rows' => 0);
        } else {
            log_message('error', 'FCM HTTP Error: ' . $httpCode . ' - Response: ' . $result);
            $invalid_token_cleared_rows = $this->isInvalidTokenResponse($response) ? $this->clearInvalidToken($tokens) : 0;
            return array('success' => false, 'driver' => 'http_v1', 'http_code' => $httpCode, 'error' => 'fcm_error', 'message' => isset($response['error']['message']) ? $response['error']['message'] : 'Firebase request failed', 'project_id' => $this->projectId, 'endpoint' => $this->fcmUrl, 'token_prefix' => substr($tokens, 0, 20) . '...', 'response' => $response, 'raw_response' => $result, 'fcm_error_code' => $fcm_error_code, 'invalid_token_cleared_rows' => $invalid_token_cleared_rows);
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
            $result = $this->sendWithReport($token, $msg, $action);
            $success = !empty($result['success']);
            $results[] = [
                'token' => substr($token, 0, 20) . '...',
                'success' => $success,
                'report' => $result,
            ];
            
            if ($success) {
                $successCount++;
            }
            
            // Small delay to prevent rate limiting
            usleep(100000); // 0.1 second pause
        }
        
        log_message('info', "Bulk notification completed. Success: $successCount/" . count($tokenArray));
        return [
            'total' => count($tokenArray),
            'success' => $successCount > 0,
            'successful' => $successCount,
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
