<?php
// Email configuration for transactional email service

class EmailConfig {
    // SMTP2GO Configuration (preferred for cost and deliverability)
    const SMTP_HOST = 'mail.smtp2go.com';
    const SMTP_PORT = 587;
    const SMTP_ENCRYPTION = 'tls';

    // Get credentials from environment variables with fallbacks
    public static function getApiKey() {
        return getenv('SMTP2GO_API_KEY') ?: getenv('EMAIL_API_KEY') ?: 'demo_key_replace_in_production';
    }

    public static function getUsername() {
        return getenv('SMTP2GO_USERNAME') ?: getenv('EMAIL_USERNAME') ?: 'demo@techhub.local';
    }

    public static function getPassword() {
        return getenv('SMTP2GO_PASSWORD') ?: getenv('EMAIL_PASSWORD') ?: 'demo_password';
    }

    public static function getFromEmail() {
        return getenv('FROM_EMAIL') ?: 'noreply@techhub.local';
    }

    public static function getFromName() {
        return getenv('FROM_NAME') ?: 'TechHub Coworking Space';
    }

    // Alternative service configurations
    public static function getSendGridConfig() {
        return [
            'api_key' => getenv('SENDGRID_API_KEY'),
            'from_email' => self::getFromEmail(),
            'from_name' => self::getFromName()
        ];
    }

    public static function getBrevoConfig() {
        return [
            'api_key' => getenv('BREVO_API_KEY'),
            'smtp_server' => 'smtp-relay.brevo.com',
            'smtp_port' => 587,
            'from_email' => self::getFromEmail(),
            'from_name' => self::getFromName()
        ];
    }

    // Email service selection
    public static function getActiveService() {
        if (getenv('SENDGRID_API_KEY')) {
            return 'sendgrid';
        } elseif (getenv('BREVO_API_KEY')) {
            return 'brevo';
        } else {
            return 'smtp2go';
        }
    }

    // Rate limiting settings
    public static function getRateLimits() {
        return [
            'smtp2go' => ['per_minute' => 300, 'per_day' => 10000],
            'sendgrid' => ['per_minute' => 600, 'per_day' => 100000],
            'brevo' => ['per_minute' => 300, 'per_day' => 40000]
        ];
    }
}
?>
