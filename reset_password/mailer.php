<?php
require_once __DIR__ . '/../includes/config.php';

class EmailJSMailer {
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $ErrorInfo = '';
    private $fromAddress = '';
    private $fromName = '';
    private $to = [];
    private $isHtml = true;

    public function setFrom($address, $name = '') {
        $this->fromAddress = $address;
        $this->fromName = $name;
    }

    public function addAddress($address) {
        if ($address) {
            $this->to[] = $address;
        }
    }

    public function isHTML($isHtml = true) {
        $this->isHtml = (bool)$isHtml;
    }

    public function send() {
        if (!EMAILJS_SERVICE_ID || !EMAILJS_TEMPLATE_ID) {
            $this->ErrorInfo = 'EmailJS configuration missing. Please set EMAILJS_SERVICE_ID and EMAILJS_TEMPLATE_ID in .env';
            return false;
        }
        // Require at least one key
        if (!EMAILJS_PRIVATE_KEY && !EMAILJS_PUBLIC_KEY) {
            $this->ErrorInfo = 'EmailJS keys missing. Set EMAILJS_PRIVATE_KEY (recommended for server) or EMAILJS_PUBLIC_KEY in .env';
            return false;
        }
        if (empty($this->to)) {
            $this->ErrorInfo = 'No recipient specified';
            return false;
        }

        $toEmail = $this->to[0];
        $payload = [
            'service_id' => EMAILJS_SERVICE_ID,
            'template_id' => EMAILJS_TEMPLATE_ID,
            'template_params' => [
                'to_email' => $toEmail,
                'from_email' => $this->fromAddress ?: MAIL_FROM_ADDRESS,
                'from_name' => $this->fromName ?: MAIL_FROM_NAME,
                'subject' => $this->Subject,
                'message' => $this->Body,
                'alt_message' => $this->AltBody,
                'is_html' => $this->isHtml ? 'true' : 'false'
            ]
        ];
        if (EMAILJS_PUBLIC_KEY) {
            $payload['user_id'] = EMAILJS_PUBLIC_KEY; // client-side identifier (optional when using Private Key)
        }

        $ch = curl_init(EMAILJS_API_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        $headers = ['Content-Type: application/json'];
        if (defined('SITE_URL') && SITE_URL) {
            $headers[] = 'Origin: ' . SITE_URL;
        }
        if (EMAILJS_PRIVATE_KEY) {
            $headers[] = 'Authorization: Bearer ' . EMAILJS_PRIVATE_KEY;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // SSL/TLS settings
        if (CURL_CAINFO_PATH) {
            curl_setopt($ch, CURLOPT_CAINFO, CURL_CAINFO_PATH);
        }
        if (RELAX_SSL_VERIFY && ENVIRONMENT === 'development') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        if (HTTP_PROXY) {
            curl_setopt($ch, CURLOPT_PROXY, HTTP_PROXY);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false) {
            $this->ErrorInfo = 'cURL error: ' . curl_error($ch);
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        $this->ErrorInfo = 'EmailJS error (HTTP ' . $httpCode . '): ' . $response;
        return false;
    }
}

$mail = new EmailJSMailer();
$mail->isHTML(true);
return $mail;