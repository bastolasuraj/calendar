<?php
// Company Settings model for branding and configuration

class CompanySettings {
    private $db;
    private $collection;
    private $cache = [];

    public function __construct() {
        $this->db = Database::getInstance()->getDb();
        $this->collection = $this->db->company_settings;
    }

    // Get setting value
    public function get($key, $default = null) {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        try {
            $setting = $this->collection->findOne(['key' => $key]);
            $value = $setting ? $setting['value'] : $default;
            $this->cache[$key] = $value;
            return $value;
        } catch (Exception $e) {
            error_log('Get setting error: ' . $e->getMessage());
            return $default;
        }
    }

    // Set setting value
    public function set($key, $value) {
        try {
            $result = $this->collection->updateOne(
                ['key' => $key],
                [
                    '$set' => [
                        'value' => $value,
                        'updated_at' => new MongoDB\BSON\UTCDateTime()
                    ],
                    '$setOnInsert' => [
                        'created_at' => new MongoDB\BSON\UTCDateTime()
                    ]
                ],
                ['upsert' => true]
            );

            if ($result->getUpsertedCount() > 0 || $result->getModifiedCount() > 0) {
                $this->cache[$key] = $value;
                return true;
            }

            return false;
        } catch (Exception $e) {
            error_log('Set setting error: ' . $e->getMessage());
            return false;
        }
    }

    // Get multiple settings
    public function getMultiple($keys) {
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = $this->get($key);
        }
        return $settings;
    }

    // Set multiple settings
    public function setMultiple($settings) {
        $success = true;
        foreach ($settings as $key => $value) {
            if (!$this->set($key, $value)) {
                $success = false;
            }
        }
        return $success;
    }

    // Get all settings
    public function getAll() {
        try {
            $settings = [];
            $cursor = $this->collection->find();

            foreach ($cursor as $setting) {
                $settings[$setting['key']] = $setting['value'];
                $this->cache[$setting['key']] = $setting['value'];
            }

            return $settings;
        } catch (Exception $e) {
            error_log('Get all settings error: ' . $e->getMessage());
            return [];
        }
    }

    // Company information methods
    public function getCompanyName() {
        return $this->get('company_name', 'TechHub Coworking Space');
    }

    public function getCompanyEmail() {
        return $this->get('company_email', 'contact@techhub.local');
    }

    public function getCompanyPhone() {
        return $this->get('company_phone', '+1 (555) 123-4567');
    }

    public function getCompanyAddress() {
        return $this->get('company_address', '123 Tech Street, Innovation City, TC 12345');
    }

    public function getCompanyWebsite() {
        return $this->get('company_website', 'https://techhub.local');
    }

    public function getCompanyLogo() {
        $logo = $this->get('company_logo', '');
        // Prepend BASE_PATH if the logo URL is relative and not empty
        if (!empty($logo) && strpos($logo, '/') === 0 && strpos($logo, '//') !== 0) {
            return BASE_PATH . $logo;
        }
        return $logo;
    }

    // Branding methods
    public function getPrimaryColor() {
        return $this->get('primary_color', '#0d6efd');
    }

    public function getSecondaryColor() {
        return $this->get('secondary_color', '#6c757d');
    }

    public function getAccentColor() {
        return $this->get('accent_color', '#198754');
    }

    public function getFaviconUrl() {
        $favicon = $this->get('favicon_url', '');
        // Prepend BASE_PATH if the favicon URL is relative and not empty
        if (!empty($favicon) && strpos($favicon, '/') === 0 && strpos($favicon, '//') !== 0) {
            return BASE_PATH . $favicon;
        }
        return $favicon;
    }

    // Working hours methods
    public function getWorkingHours() {
        return [
            'start' => $this->get('working_hours_start', '08:00'),
            'end' => $this->get('working_hours_end', '17:00'),
            'slot_duration' => (int)$this->get('slot_duration_hours', 2)
        ];
    }

    public function getWorkingDays() {
        return json_decode($this->get('working_days', '["1","2","3","4","5"]'), true);
    }

    // Booking settings
    public function getMaxAdvanceBookingDays() {
        return (int)$this->get('max_advance_booking_days', 30);
    }

    public function getMinAdvanceBookingHours() {
        return (int)$this->get('min_advance_booking_hours', 2);
    }

    public function isBookingApprovalRequired() {
        return (bool)$this->get('require_booking_approval', true);
    }

    public function allowBookingUpdates() {
        return (bool)$this->get('allow_booking_updates', true);
    }

    // Email settings
    public function getEmailSettings() {
        return [
            'from_name' => $this->get('email_from_name', $this->getCompanyName()),
            'from_email' => $this->get('email_from_email', $this->getCompanyEmail()),
            'reply_to' => $this->get('email_reply_to', $this->getCompanyEmail()),
            'footer_text' => $this->get('email_footer_text', $this->getCompanyName() . ' - ' . $this->getCompanyWebsite())
        ];
    }

    // Notification settings
    public function getNotificationSettings() {
        return [
            'admin_new_booking' => (bool)$this->get('notify_admin_new_booking', true),
            'admin_booking_update' => (bool)$this->get('notify_admin_booking_update', true),
            'user_booking_confirmation' => (bool)$this->get('notify_user_confirmation', true),
            'user_status_update' => (bool)$this->get('notify_user_status_update', true),
            'user_booking_reminder' => (bool)$this->get('notify_user_reminder', true),
            'reminder_hours_before' => (int)$this->get('reminder_hours_before', 24)
        ];
    }

    // Initialize default settings
    public function initializeDefaults() {
        $defaults = [
            // Company Information
            'company_name' => 'TechHub Coworking Space',
            'company_email' => 'contact@techhub.local',
            'company_phone' => '+1 (555) 123-4567',
            'company_address' => '123 Tech Street, Innovation City, TC 12345',
            'company_website' => 'https://techhub.local',
            'company_description' => 'A modern coworking space for technology professionals and entrepreneurs.',

            // Branding
            'primary_color' => '#0d6efd',
            'secondary_color' => '#6c757d',
            'accent_color' => '#198754',
            'theme' => 'light',

            // Working Hours
            'working_hours_start' => '08:00',
            'working_hours_end' => '17:00',
            'slot_duration_hours' => 2,
            'working_days' => '["1","2","3","4","5"]', // Monday to Friday

            // Booking Settings
            'max_advance_booking_days' => 30,
            'min_advance_booking_hours' => 2,
            'require_booking_approval' => true,
            'allow_booking_updates' => true,
            'max_concurrent_bookings' => 1,

            // Email Settings
            'email_from_name' => 'TechHub Coworking Space',
            'email_from_email' => 'noreply@techhub.local',
            'email_reply_to' => 'contact@techhub.local',
            'email_footer_text' => 'TechHub Coworking Space - https://techhub.local',

            // Notification Settings
            'notify_admin_new_booking' => true,
            'notify_admin_booking_update' => true,
            'notify_user_confirmation' => true,
            'notify_user_status_update' => true,
            'notify_user_reminder' => true,
            'reminder_hours_before' => 24,

            // System Settings
            'timezone' => 'America/Toronto',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'language' => 'en',
            'maintenance_mode' => false
        ];

        foreach ($defaults as $key => $value) {
            // Only set if not already exists
            if ($this->get($key) === null) {
                $this->set($key, $value);
            }
        }

        return true;
    }

    // Get timezone
    public function getTimezone() {
        return $this->get('timezone', 'America/Toronto');
    }

    // Get date format
    public function getDateFormat() {
        return $this->get('date_format', 'Y-m-d');
    }

    // Get time format
    public function getTimeFormat() {
        return $this->get('time_format', 'H:i');
    }

    // Check if maintenance mode is enabled
    public function isMaintenanceMode() {
        return (bool)$this->get('maintenance_mode', false);
    }

    // Get formatted settings for display
    public function getFormattedSettings() {
        $allSettings = $this->getAll();

        return [
            'company' => [
                'name' => $allSettings['company_name'] ?? '',
                'email' => $allSettings['company_email'] ?? '',
                'phone' => $allSettings['company_phone'] ?? '',
                'address' => $allSettings['company_address'] ?? '',
                'website' => $allSettings['company_website'] ?? '',
                'description' => $allSettings['company_description'] ?? ''
            ],
            'branding' => [
                'primary_color' => $allSettings['primary_color'] ?? '#0d6efd',
                'secondary_color' => $allSettings['secondary_color'] ?? '#6c757d',
                'accent_color' => $allSettings['accent_color'] ?? '#198754',
                'logo' => $this->getCompanyLogo(), // Use the method that prepends BASE_PATH
                'favicon' => $this->getFaviconUrl() // Use the method that prepends BASE_PATH
            ],
            'working_hours' => [
                'start' => $allSettings['working_hours_start'] ?? '08:00',
                'end' => $allSettings['working_hours_end'] ?? '17:00',
                'slot_duration' => $allSettings['slot_duration_hours'] ?? 2,
                'working_days' => json_decode($allSettings['working_days'] ?? '["1","2","3","4","5"]', true)
            ],
            'booking' => [
                'max_advance_days' => $allSettings['max_advance_booking_days'] ?? 30,
                'min_advance_hours' => $allSettings['min_advance_booking_hours'] ?? 2,
                'require_approval' => $allSettings['require_booking_approval'] ?? true,
                'allow_updates' => $allSettings['allow_booking_updates'] ?? true
            ]
        ];
    }

    // Clear cache
    public function clearCache() {
        $this->cache = [];
    }
}
