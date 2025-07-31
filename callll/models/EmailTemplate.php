<?php
// Email Template model for customizable email notifications

class EmailTemplate {
    private $db;
    private $collection;

    const TYPE_BOOKING_CONFIRMATION = 'booking_confirmation';
    const TYPE_STATUS_UPDATE = 'status_update';
    const TYPE_BOOKING_REMINDER = 'booking_reminder';
    const TYPE_ADMIN_NOTIFICATION = 'admin_notification';

    public function __construct() {
        $this->db = Database::getInstance()->getDb();
        $this->collection = $this->db->email_templates;
    }

    // Get template by type
    public function getTemplate($type) {
        try {
            $template = $this->collection->findOne(['type' => $type, 'active' => true]);

            if (!$template) {
                return $this->getDefaultTemplate($type);
            }

            return $template;
        } catch (Exception $e) {
            error_log('Get template error: ' . $e->getMessage());
            return $this->getDefaultTemplate($type);
        }
    }

    // Save template
    public function saveTemplate($type, $subject, $htmlBody, $textBody = '', $name = '') {
        try {
            // Deactivate existing template of this type
            $this->collection->updateMany(
                ['type' => $type, 'active' => true],
                ['$set' => ['active' => false]]
            );

            $template = [
                'type' => $type,
                'name' => $name ?: ucwords(str_replace('_', ' ', $type)),
                'subject' => $subject,
                'html_body' => $htmlBody,
                'text_body' => $textBody ?: strip_tags($htmlBody),
                'active' => true,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'created_by' => $_SESSION['user_id'] ?? null,
                'variables' => $this->extractVariables($subject . ' ' . $htmlBody)
            ];

            $result = $this->collection->insertOne($template);

            return $result->getInsertedCount() > 0 ? $template : false;

        } catch (Exception $e) {
            error_log('Save template error: ' . $e->getMessage());
            return false;
        }
    }

    // Get all templates
    public function getAllTemplates() {
        try {
            return $this->collection->find([], ['sort' => ['type' => 1, 'created_at' => -1]]);
        } catch (Exception $e) {
            error_log('Get all templates error: ' . $e->getMessage());
            return [];
        }
    }

    // Get template by ID
    public function getTemplateById($id) {
        try {
            return $this->collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
        } catch (Exception $e) {
            error_log('Get template by ID error: ' . $e->getMessage());
            return null;
        }
    }

    // Delete template
    public function deleteTemplate($id) {
        try {
            $result = $this->collection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
            return $result->getDeletedCount() > 0;
        } catch (Exception $e) {
            error_log('Delete template error: ' . $e->getMessage());
            return false;
        }
    }

    // Render template with variables
    public function renderTemplate($type, $variables = []) {
        $template = $this->getTemplate($type);
        if (!$template) {
            return null;
        }

        $subject = $this->replaceVariables($template['subject'], $variables);
        $htmlBody = $this->replaceVariables($template['html_body'], $variables);
        $textBody = $this->replaceVariables($template['text_body'], $variables);

        return [
            'subject' => $subject,
            'html_body' => $htmlBody,
            'text_body' => $textBody
        ];
    }

    // Replace variables in template content
    private function replaceVariables($content, $variables) {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        // Replace remaining unreplaced variables with empty string
        $content = preg_replace('/\{\{[^}]+\}\}/', '', $content);

        return $content;
    }

    // Extract variables from template content
    private function extractVariables($content) {
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
        return array_unique($matches[1]);
    }

    // Get available variables for template type
    public function getAvailableVariables($type) {
        $commonVariables = [
            'company_name' => 'Company name',
            'company_email' => 'Company email',
            'company_phone' => 'Company phone',
            'company_website' => 'Company website',
            'current_date' => 'Current date',
            'current_time' => 'Current time'
        ];

        $bookingVariables = [
            'booking_id' => 'Booking access code',
            'customer_name' => 'Customer name',
            'customer_email' => 'Customer email',
            'booking_date' => 'Booking date',
            'booking_time' => 'Booking time',
            'booking_datetime' => 'Full booking date and time',
            'booking_status' => 'Booking status',
            'booking_url' => 'URL to view/manage booking' // Added this variable
        ];

        switch ($type) {
            case self::TYPE_BOOKING_CONFIRMATION:
            case self::TYPE_STATUS_UPDATE:
            case self::TYPE_BOOKING_REMINDER:
                return array_merge($commonVariables, $bookingVariables);

            case self::TYPE_ADMIN_NOTIFICATION:
                return array_merge($commonVariables, $bookingVariables, [
                    'admin_dashboard_url' => 'Admin dashboard URL'
                ]);

            default:
                return $commonVariables;
        }
    }

    // Initialize default templates
    public function initializeDefaultTemplates() {
        $templates = [
            self::TYPE_BOOKING_CONFIRMATION => [
                'name' => 'Booking Confirmation',
                'subject' => 'Booking Confirmation - {{company_name}}',
                'html_body' => $this->getDefaultBookingConfirmationHtml(),
                'text_body' => $this->getDefaultBookingConfirmationText()
            ],
            self::TYPE_STATUS_UPDATE => [
                'name' => 'Booking Status Update',
                'subject' => 'Booking Status Update - {{booking_status}}',
                'html_body' => $this->getDefaultStatusUpdateHtml(),
                'text_body' => $this->getDefaultStatusUpdateText()
            ],
            self::TYPE_BOOKING_REMINDER => [
                'name' => 'Booking Reminder',
                'subject' => 'Booking Reminder - Tomorrow at {{booking_time}}',
                'html_body' => $this->getDefaultReminderHtml(),
                'text_body' => $this->getDefaultReminderText()
            ],
            self::TYPE_ADMIN_NOTIFICATION => [
                'name' => 'Admin New Booking Notification',
                'subject' => 'New Booking Request - {{customer_name}}',
                'html_body' => $this->getDefaultAdminNotificationHtml(),
                'text_body' => $this->getDefaultAdminNotificationText()
            ]
        ];

        foreach ($templates as $type => $template) {
            // Check if template already exists
            if (!$this->collection->findOne(['type' => $type])) {
                $this->saveTemplate(
                    $type,
                    $template['subject'],
                    $template['html_body'],
                    $template['text_body'],
                    $template['name']
                );
            }
        }

        return true;
    }

    // Get default template if none exists
    private function getDefaultTemplate($type) {
        $defaults = [
            self::TYPE_BOOKING_CONFIRMATION => [
                'type' => $type,
                'subject' => 'Booking Confirmation - {{company_name}}',
                'html_body' => $this->getDefaultBookingConfirmationHtml(),
                'text_body' => $this->getDefaultBookingConfirmationText()
            ],
            self::TYPE_STATUS_UPDATE => [
                'type' => $type,
                'subject' => 'Booking Status Update - {{booking_status}}',
                'html_body' => $this->getDefaultStatusUpdateHtml(),
                'text_body' => $this->getDefaultStatusUpdateText()
            ],
            self::TYPE_BOOKING_REMINDER => [
                'type' => $type,
                'subject' => 'Booking Reminder - Tomorrow at {{booking_time}}',
                'html_body' => $this->getDefaultReminderHtml(),
                'text_body' => $this->getDefaultReminderText()
            ],
            self::TYPE_ADMIN_NOTIFICATION => [
                'type' => $type,
                'subject' => 'New Booking Request - {{customer_name}}',
                'html_body' => $this->getDefaultAdminNotificationHtml(),
                'text_body' => $this->getDefaultAdminNotificationText()
            ]
        ];

        return $defaults[$type] ?? null;
    }

    // Default template content methods
    private function getDefaultBookingConfirmationHtml() {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0d6efd; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fa; }
        .booking-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
        .btn { display: inline-block; padding: 10px 20px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Confirmation</h1>
            <p>{{company_name}}</p>
        </div>
        <div class="content">
            <h2>Hello {{customer_name}},</h2>
            <p>Thank you for your booking request! We have received your submission and it is currently being reviewed.</p>
            
            <div class="booking-details">
                <h3>Booking Details:</h3>
                <p><strong>Access Code:</strong> {{booking_id}}</p>
                <p><strong>Date & Time:</strong> {{booking_datetime}}</p>
                <p><strong>Status:</strong> {{booking_status}}</p>
            </div>
            
            <p>You will receive another email once your booking has been reviewed and approved.</p>
            <p>You can check the status of your booking anytime using your access code.</p>
            
            <p style="text-align: center;">
                <a href="{{booking_url}}" class="btn">Check Booking Status</a>
            </p>
        </div>
        <div class="footer">
            <p>{{company_name}}<br>
            {{company_email}} | {{company_phone}}<br>
            {{company_website}}</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getDefaultBookingConfirmationText() {
        return 'Hello {{customer_name}},

Thank you for your booking request! We have received your submission and it is currently being reviewed.

Booking Details:
Access Code: {{booking_id}}
Date & Time: {{booking_datetime}}
Status: {{booking_status}}

You will receive another email once your booking has been reviewed and approved.
You can check the status of your booking anytime using your access code at: {{booking_url}}

Best regards,
{{company_name}}
{{company_email}} | {{company_phone}}
{{company_website}}';
    }

    private function getDefaultStatusUpdateHtml() {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Status Update</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0d6efd; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fa; }
        .status-approved { background: #d1edff; border-left: 4px solid #0d6efd; padding: 15px; margin: 15px 0; }
        .status-rejected { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 15px 0; }
        .booking-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Status Update</h1>
            <p>{{company_name}}</p>
        </div>
        <div class="content">
            <h2>Hello {{customer_name}},</h2>
            
            <div class="status-{{booking_status}}">
                <h3>Your booking has been {{booking_status}}!</h3>
            </div>
            
            <div class="booking-details">
                <h3>Booking Details:</h3>
                <p><strong>Access Code:</strong> {{booking_id}}</p>
                <p><strong>Date & Time:</strong> {{booking_datetime}}</p>
                <p><strong>Status:</strong> {{booking_status}}</p>
            </div>
            
            <p>If you have any questions, please don\'t hesitate to contact us.</p>
            <p style="text-align: center;">
                <a href="{{booking_url}}" class="btn">View Booking Details</a>
            </p>
        </div>
        <div class="footer">
            <p>{{company_name}}<br>
            {{company_email}} | {{company_phone}}<br>
            {{company_website}}</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getDefaultStatusUpdateText() {
        return 'Hello {{customer_name}},

Your booking has been {{booking_status}}!

Booking Details:
Access Code: {{booking_id}}
Date & Time: {{booking_datetime}}
Status: {{booking_status}}

If you have any questions, please don\'t hesitate to contact us.
View booking details: {{booking_url}}

Best regards,
{{company_name}}
{{company_email}} | {{company_phone}}
{{company_website}}';
    }

    private function getDefaultReminderHtml() {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Reminder</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #198754; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fa; }
        .reminder { background: #d1ecf1; border-left: 4px solid #0dcaf0; padding: 15px; margin: 15px 0; }
        .booking-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Booking Reminder</h1>
            <p>{{company_name}}</p>
        </div>
        <div class="content">
            <h2>Hello {{customer_name}},</h2>
            
            <div class="reminder">
                <h3>Don\'t forget about your upcoming booking!</h3>
                <p>This is a friendly reminder about your scheduled session.</p>
            </div>
            
            <div class="booking-details">
                <h3>Booking Details:</h3>
                <p><strong>Access Code:</strong> {{booking_id}}</p>
                <p><strong>Date & Time:</strong> {{booking_datetime}}</p>
            </div>
            
            <p>We look forward to seeing you soon!</p>
            <p style="text-align: center;">
                <a href="{{booking_url}}" class="btn">View Booking Details</a>
            </p>
        </div>
        <div class="footer">
            <p>{{company_name}}<br>
            {{company_email}} | {{company_phone}}<br>
            {{company_website}}</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getDefaultReminderText() {
        return 'Hello {{customer_name}},

Don\'t forget about your upcoming booking!

This is a friendly reminder about your scheduled session.

Booking Details:
Access Code: {{booking_id}}
Date & Time: {{booking_datetime}}

We look forward to seeing you soon!
View booking details: {{booking_url}}

Best regards,
{{company_name}}
{{company_email}} | {{company_phone}}
{{company_website}}';
    }

    private function getDefaultAdminNotificationHtml() {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Booking Request</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #fd7e14; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f8f9fa; }
        .booking-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
        .btn { display: inline-block; padding: 10px 20px; background: #fd7e14; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Booking Request</h1>
            <p>{{company_name}} Admin</p>
        </div>
        <div class="content">
            <h2>New booking request received</h2>
            <p>A new booking request has been submitted and requires your review.</p>
            
            <div class="booking-details">
                <h3>Booking Details:</h3>
                <p><strong>Customer:</strong> {{customer_name}} ({{customer_email}})</p>
                <p><strong>Access Code:</strong> {{booking_id}}</p>
                <p><strong>Date & Time:</strong> {{booking_datetime}}</p>
                <p><strong>Status:</strong> {{booking_status}}</p>
            </div>
            
            <p style="text-align: center;">
                <a href="{{admin_dashboard_url}}" class="btn">Review in Admin Dashboard</a>
            </p>
        </div>
        <div class="footer">
            <p>{{company_name}} Admin System</p>
        </div>
    </div>
</body>
</html>';
    }

    private function getDefaultAdminNotificationText() {
        return 'New booking request received

A new booking request has been submitted and requires your review.

Booking Details:
Customer: {{customer_name}} ({{customer_email}})
Access Code: {{booking_id}}
Date & Time: {{booking_datetime}}
Status: {{booking_status}}

Please review the booking in your admin dashboard: {{admin_dashboard_url}}

{{company_name}} Admin System';
    }
}
