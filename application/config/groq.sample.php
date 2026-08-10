<?php defined('BASEPATH') OR exit('No direct script access allowed');

// Set this only on the server if Hostinger does not provide environment variables.
// Do not commit a real API key to source control.
$config['groq_api_key'] = '';
$groq_environment_key = getenv('GROQ_API_KEY');
if (empty($groq_environment_key) && isset($_ENV['GROQ_API_KEY'])) {
    $groq_environment_key = $_ENV['GROQ_API_KEY'];
}
if (!empty($groq_environment_key)) {
    $config['groq_api_key'] = $groq_environment_key;
}
$config['groq_endpoint'] = 'https://api.groq.com/openai/v1/chat/completions';
$config['groq_model'] = 'openai/gpt-oss-120b';
$config['ai_widget_context_messages'] = 10;
$config['ai_widget_context_chars'] = 24000;