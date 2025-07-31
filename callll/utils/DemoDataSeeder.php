<?php
// Demo data seeder for setting up sample data

class DemoDataSeeder {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getDb();
    }

    public function seedAll() {
        try {
            $this->seedUsers();
            $this->seedCompanySettings();
            $this->seedFormConfigurations();
            $this->seedEmailTemplates();
            $this->seedBookings();

            return ['success' => true, 'message' => 'Demo data seeded successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error seeding data: ' . $e->getMessage()];
        }
    }

    private function seedUsers() {
        $users = $this->db->users;

        // Clear existing users
        $users->deleteMany([]);

        // Super Admin
        $users->insertOne([
            'email' => 'admin@demo.com',
            'password' => password_hash('AdminDemo123!', PASSWORD_DEFAULT),
            'role' => User::ROLE_SUPER_ADMIN,
            'active' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'permissions' => [
                'view_bookings', 'manage_bookings', 'view_analytics', 'manage_users',
                'manage_company_settings', 'manage_form_builder', 'manage_email_templates',
                'export_data', 'system_settings'
            ]
        ]);

        // Regular Admin
        $users->insertOne([
            'email' => 'manager@demo.com',
            'password' => password_hash('ManagerDemo123!', PASSWORD_DEFAULT),
            'role' => User::ROLE_ADMIN,
            'active' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'permissions' => [
                'view_bookings', 'manage_bookings', 'view_analytics',
                'manage_form_builder', 'manage_email_templates', 'export_data'
            ]
        ]);
    }

    private function seedCompanySettings() {
        $settings = $this->db->company_settings;

        // Clear existing settings
        $settings->deleteMany([]);

        $defaultSettings = [
            // Company Info
            ['key' => 'company_name', 'value' => 'TechHub Coworking Space'],
            ['key' => 'company_email', 'value' => 'info@techhub.com'],
            ['key' => 'company_phone', 'value' => '+1 (555) 123-4567'],
            ['key' => 'company_address', 'value' => '123 Tech Street, Innovation City, TC 12345'],
            ['key' => 'company_website', 'value' => 'https://techhub.com'],
            ['key' => 'timezone', 'value' => 'America/Toronto'],

            // Business Hours
            ['key' => 'business_hours_start', 'value' => '08:00'],
            ['key' => 'business_hours_end', 'value' => '17:00'],
            ['key' => 'slot_duration_hours', 'value' => 2],
            ['key' => 'business_days', 'value' => ['1', '2', '3', '4', '5']],

            // Booking Settings
            ['key' => 'max_advance_days', 'value' => 30],
            ['key' => 'min_advance_hours', 'value' => 2],
            ['key' => 'auto_approve', 'value' => false],
            ['key' => 'allow_cancellation', 'value' => true],
            ['key' => 'cancellation_hours', 'value' => 24],
            ['key' => 'require_approval', 'value' => true],

            // Email Settings
            ['key' => 'email_enabled', 'value' => true],
            ['key' => 'smtp_host', 'value' => 'mail.smtp2go.com'],
            ['key' => 'smtp_port', 'value' => 587],
            ['key' => 'smtp_encryption', 'value' => 'tls'],
            ['key' => 'from_name', 'value' => 'TechHub Booking System'],
            ['key' => 'from_email', 'value' => 'noreply@techhub.com'],

            // Branding
            ['key' => 'primary_color', 'value' => '#0d6efd'],
            ['key' => 'secondary_color', 'value' => '#6c757d'],
            ['key' => 'success_color', 'value' => '#198754'],
            ['key' => 'warning_color', 'value' => '#ffc107'],
            ['key' => 'danger_color', 'value' => '#dc3545'],
            ['key' => 'show_powered_by', 'value' => true]
        ];

        foreach ($defaultSettings as $setting) {
            $setting['updated_at'] = new MongoDB\BSON\UTCDateTime();
            $settings->insertOne($setting);
        }
    }

    private function seedFormConfigurations() {
        $forms = $this->db->form_configurations;

        // Clear existing forms
        $forms->deleteMany([]);

        // Default booking form
        $defaultFields = [
            [
                'type' => 'email',
                'name' => 'email',
                'label' => 'Email Address',
                'required' => true,
                'placeholder' => 'Enter your email address',
                'order' => 1
            ],
            [
                'type' => 'text',
                'name' => 'laptop_tag',
                'label' => 'Laptop Tag',
                'required' => true,
                'placeholder' => 'Enter laptop tag number (e.g., LT-001)',
                'order' => 2,
                'settings' => [
                    'pattern' => '^LT-[0-9]{3}$',
                    'maxlength' => 10
                ]
            ],
            [
                'type' => 'phone',
                'name' => 'phone',
                'label' => 'Phone Number',
                'required' => false,
                'placeholder' => 'Enter your phone number',
                'order' => 3
            ],
            [
                'type' => 'select',
                'name' => 'laptop_model',
                'label' => 'Laptop Model',
                'required' => true,
                'order' => 4,
                'options' => [
                    ['value' => 'macbook_pro', 'label' => 'MacBook Pro'],
                    ['value' => 'macbook_air', 'label' => 'MacBook Air'],
                    ['value' => 'dell_xps', 'label' => 'Dell XPS'],
                    ['value' => 'thinkpad', 'label' => 'ThinkPad'],
                    ['value' => 'surface_laptop', 'label' => 'Surface Laptop'],
                    ['value' => 'other', 'label' => 'Other']
                ]
            ],
            [
                'type' => 'select',
                'name' => 'department',
                'label' => 'Department',
                'required' => true,
                'order' => 5,
                'options' => [
                    ['value' => 'engineering', 'label' => 'Engineering'],
                    ['value' => 'design', 'label' => 'Design'],
                    ['value' => 'marketing', 'label' => 'Marketing'],
                    ['value' => 'sales', 'label' => 'Sales'],
                    ['value' => 'hr', 'label' => 'Human Resources'],
                    ['value' => 'finance', 'label' => 'Finance'],
                    ['value' => 'other', 'label' => 'Other']
                ]
            ],
            [
                'type' => 'textarea',
                'name' => 'purpose',
                'label' => 'Purpose of Booking',
                'required' => false,
                'placeholder' => 'Briefly describe the purpose of your booking',
                'order' => 6,
                'settings' => [
                    'rows' => 3,
                    'maxlength' => 500
                ]
            ]
        ];

        $formId = $forms->insertOne([
            'name' => 'TechHub Booking Form',
            'description' => 'Comprehensive booking form for TechHub Coworking Space',
            'fields' => $defaultFields,
            'is_active' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'version' => 1
        ])->getInsertedId();

        // Advanced form example
        $advancedFields = [
            [
                'type' => 'email',
                'name' => 'email',
                'label' => 'Email Address',
                'required' => true,
                'placeholder' => 'Enter your email',
                'order' => 1
            ],
            [
                'type' => 'text',
                'name' => 'laptop_tag',
                'label' => 'Equipment ID',
                'required' => true,
                'placeholder' => 'Enter equipment identifier',
                'order' => 2
            ],
            [
                'type' => 'text',
                'name' => 'name',
                'label' => 'Full Name',
                'required' => true,
                'placeholder' => 'Enter your full name',
                'order' => 3
            ],
            [
                'type' => 'phone',
                'name' => 'phone',
                'label' => 'Contact Number',
                'required' => true,
                'placeholder' => '+1 (555) 123-4567',
                'order' => 4
            ],
            [
                'type' => 'select',
                'name' => 'project_type',
                'label' => 'Project Type',
                'required' => true,
                'order' => 5,
                'options' => [
                    ['value' => 'development', 'label' => 'Software Development'],
                    ['value' => 'design', 'label' => 'UI/UX Design'],
                    ['value' => 'research', 'label' => 'Research & Analysis'],
                    ['value' => 'testing', 'label' => 'Testing & QA'],
                    ['value' => 'presentation', 'label' => 'Presentation'],
                    ['value' => 'training', 'label' => 'Training Session']
                ]
            ],
            [
                'type' => 'checkbox',
                'name' => 'requirements',
                'label' => 'Additional Requirements',
                'required' => false,
                'order' => 6,
                'options' => [
                    ['value' => 'monitor', 'label' => 'External Monitor'],
                    ['value' => 'keyboard', 'label' => 'External Keyboard'],
                    ['value' => 'mouse', 'label' => 'External Mouse'],
                    ['value' => 'webcam', 'label' => 'HD Webcam'],
                    ['value' => 'headset', 'label' => 'Headset'],
                    ['value' => 'cables', 'label' => 'HDMI/USB Cables']
                ]
            ],
            [
                'type' => 'number',
                'name' => 'team_size',
                'label' => 'Team Size',
                'required' => false,
                'order' => 7,
                'settings' => [
                    'min' => 1,
                    'max' => 20
                ]
            ]
        ];

        $forms->insertOne([
            'name' => 'Advanced Booking Form',
            'description' => 'Comprehensive form with all field types',
            'fields' => $advancedFields,
            'is_active' => false,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'version' => 1
        ]);
    }

    private function seedEmailTemplates() {
        $templates = $this->db->email_templates;

        // Clear existing templates
        $templates->deleteMany([]);

        $defaultTemplates = [
            [
                'type' => EmailTemplate::TYPE_BOOKING_CONFIRMATION,
                'name' => 'TechHub Confirmation',
                'subject' => 'Booking Confirmation - {{company_name}}',
                'body' => '<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 10px; color: white; margin-bottom: 20px;">
                    <h1 style="margin: 0; font-size: 28px;">Booking Confirmed!</h1>
                    <p style="margin: 10px 0 0 0; opacity: 0.9;">Thank you for choosing {{company_name}}</p>
                </div>
                
                <p>Dear {{user_name}},</p>
                
                <p>Your booking request has been received and is currently <strong>{{booking_status}}</strong>. Here are your booking details:</p>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Access Code:</td>
                            <td style="padding: 8px 0; color: #0d6efd; font-family: monospace; font-size: 16px;">{{booking_id}}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Date & Time:</td>
                            <td style="padding: 8px 0;">{{booking_datetime}}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Equipment:</td>
                            <td style="padding: 8px 0;">{{laptop_tag}}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Department:</td>
                            <td style="padding: 8px 0;">{{department}}</td>
                        </tr>
                    </table>
                </div>
                
                <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; border-left: 4px solid #2196f3; margin: 20px 0;">
                    <p style="margin: 0; color: #1565c0;">
                        <strong>Important:</strong> Please save your access code for future reference. You can use it to view or modify your booking.
                    </p>
                </div>
                
                <p>
                    <a href="{{booking_url}}" style="display: inline-block; background: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                        Manage Your Booking
                    </a>
                </p>
                
                <p>If you have any questions, please don\'t hesitate to contact us.</p>',
                'is_active' => true,
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ],
            [
                'type' => EmailTemplate::TYPE_STATUS_UPDATE,
                'name' => 'TechHub Status Update',
                'subject' => 'Booking {{booking_status}} - {{company_name}}',
                'body' => '<div style="background: {{status_color}}; padding: 30px; border-radius: 10px; color: white; margin-bottom: 20px;">
                    <h1 style="margin: 0; font-size: 28px;">Booking {{booking_status|title}}</h1>
                    <p style="margin: 10px 0 0 0; opacity: 0.9;">Status update from {{company_name}}</p>
                </div>
                
                <p>Dear {{user_name}},</p>
                
                <p>We wanted to update you on the status of your booking. Your booking has been <strong>{{booking_status}}</strong>.</p>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Access Code:</td>
                            <td style="padding: 8px 0; color: #0d6efd; font-family: monospace; font-size: 16px;">{{booking_id}}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Date & Time:</td>
                            <td style="padding: 8px 0;">{{booking_datetime}}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Status:</td>
                            <td style="padding: 8px 0;"><span style="background: {{status_color}}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; text-transform: uppercase;">{{booking_status}}</span></td>
                        </tr>
                    </table>
                </div>
                
                <p>
                    <a href="{{booking_url}}" style="display: inline-block; background: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                        View Booking Details
                    </a>
                </p>',
                'is_active' => true,
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ],
            [
                'type' => EmailTemplate::TYPE_BOOKING_UPDATE,
                'name' => 'TechHub Booking Update',
                'subject' => 'Booking Updated - Pending Reapproval',
                'body' => '<div style="background: #ff9800; padding: 30px; border-radius: 10px; color: white; margin-bottom: 20px;">
                    <h1 style="margin: 0; font-size: 28px;">Booking Updated</h1>
                    <p style="margin: 10px 0 0 0; opacity: 0.9;">Your booking requires reapproval</p>
                </div>
                
                <p>Dear {{user_name}},</p>
                
                <p>Your booking has been successfully updated. Since you\'ve made changes to your booking, it will need to be reviewed and approved again by our team.</p>
                
                <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 20px 0;">
                    <p style="margin: 0; color: #856404;">
                        <strong>Status:</strong> Your booking is now pending approval. You will receive another notification once it has been reviewed.
                    </p>
                </div>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Access Code:</td>
                            <td style="padding: 8px 0; color: #0d6efd; font-family: monospace; font-size: 16px;">{{booking_id}}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; font-weight: bold; color: #495057;">Updated Date & Time:</td>
                            <td style="padding: 8px 0;">{{booking_datetime}}</td>
                        </tr>
                    </table>
                </div>
                
                <p>
                    <a href="{{booking_url}}" style="display: inline-block; background: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">
                        Check Booking Status
                    </a>
                </p>',
                'is_active' => true,
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ];

        foreach ($defaultTemplates as $template) {
            $templates->insertOne($template);
        }
    }

    private function seedBookings() {
        $bookings = $this->db->bookings;

        // Clear existing bookings
        $bookings->deleteMany([]);

        // Sample booking data
        $sampleUsers = [
            ['name' => 'Alex Johnson', 'email' => 'alex.johnson@example.com', 'phone' => '+1 (555) 123-4567', 'department' => 'engineering'],
            ['name' => 'Sarah Chen', 'email' => 'sarah.chen@example.com', 'phone' => '+1 (555) 234-5678', 'department' => 'design'],
            ['name' => 'Michael Brown', 'email' => 'michael.brown@example.com', 'phone' => '+1 (555) 345-6789', 'department' => 'marketing'],
            ['name' => 'Emily Davis', 'email' => 'emily.davis@example.com', 'phone' => '+1 (555) 456-7890', 'department' => 'sales'],
            ['name' => 'David Wilson', 'email' => 'david.wilson@example.com', 'phone' => '+1 (555) 567-8901', 'department' => 'hr'],
            ['name' => 'Lisa Garcia', 'email' => 'lisa.garcia@example.com', 'phone' => '+1 (555) 678-9012', 'department' => 'finance'],
            ['name' => 'James Miller', 'email' => 'james.miller@example.com', 'phone' => '+1 (555) 789-0123', 'department' => 'engineering'],
            ['name' => 'Jennifer Taylor', 'email' => 'jennifer.taylor@example.com', 'phone' => '+1 (555) 890-1234', 'department' => 'design'],
            ['name' => 'Robert Anderson', 'email' => 'robert.anderson@example.com', 'phone' => '+1 (555) 901-2345', 'department' => 'marketing'],
            ['name' => 'Maria Rodriguez', 'email' => 'maria.rodriguez@example.com', 'phone' => '+1 (555) 012-3456', 'department' => 'sales']
        ];

        $laptopModels = ['macbook_pro', 'macbook_air', 'dell_xps', 'thinkpad', 'surface_laptop'];
        $purposes = [
            'Development sprint planning session',
            'Client presentation preparation',
            'UI/UX design workshop',
            'Code review and testing',
            'Team collaboration meeting',
            'Training session setup',
            'Product demo preparation',
            'Research and analysis work',
            'Documentation writing',
            'Video conference setup'
        ];

        $statuses = ['approved', 'pending', 'rejected'];
        $statusWeights = [0.6, 0.3, 0.1]; // 60% approved, 30% pending, 10% rejected

        // Get the active form ID
        $forms = $this->db->form_configurations;
        $activeForm = $forms->findOne(['is_active' => true]);
        $formId = $activeForm ? (string)$activeForm['_id'] : null;

        for ($i = 0; $i < 15; $i++) {
            $user = $sampleUsers[$i % count($sampleUsers)];
            $laptopTag = 'LT-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);

            // Generate random booking date (within last 30 days to next 30 days)
            $randomDays = rand(-30, 30);
            $randomHour = rand(8, 16); // Business hours
            $randomMinute = rand(0, 1) * 60; // 0 or 60 minutes

            $bookingDate = new DateTime();
            $bookingDate->modify("{$randomDays} days");
            $bookingDate->setTime($randomHour, $randomMinute, 0);

            // Skip weekends
            if ($bookingDate->format('N') >= 6) {
                $bookingDate->modify('next Monday');
            }

            // Weighted random status selection
            $rand = mt_rand() / mt_getrandmax();
            $status = 'pending';
            $cumulative = 0;
            foreach ($statusWeights as $index => $weight) {
                $cumulative += $weight;
                if ($rand <= $cumulative) {
                    $status = $statuses[$index];
                    break;
                }
            }

            $formData = [
                'email' => $user['email'],
                'laptop_tag' => $laptopTag,
                'phone' => $user['phone'],
                'laptop_model' => $laptopModels[array_rand($laptopModels)],
                'department' => $user['department'],
                'purpose' => $purposes[array_rand($purposes)]
            ];

            $createdAt = clone $bookingDate;
            $createdAt->modify('-' . rand(1, 7) . ' days'); // Created 1-7 days before booking

            $bookingData = [
                'access_code' => $this->generateAccessCode(),
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'laptop_tag' => $laptopTag,
                'booking_datetime' => new MongoDB\BSON\UTCDateTime($bookingDate->getTimestamp() * 1000),
                'status' => $status,
                'form_data' => $formData,
                'form_id' => $formId,
                'created_at' => new MongoDB\BSON\UTCDateTime($createdAt->getTimestamp() * 1000)
            ];

            if ($status !== 'pending') {
                $updatedAt = clone $createdAt;
                $updatedAt->modify('+' . rand(1, 3) . ' hours');
                $bookingData['updated_at'] = new MongoDB\BSON\UTCDateTime($updatedAt->getTimestamp() * 1000);
            }

            $bookings->insertOne($bookingData);
        }
    }

    private function generateAccessCode($length = 15) {
        return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/62))), 1, $length);
    }

    public function clearAllData() {
        try {
            $collections = ['users', 'bookings', 'form_configurations', 'company_settings', 'email_templates'];

            foreach ($collections as $collectionName) {
                $this->db->selectCollection($collectionName)->deleteMany([]);
            }

            return ['success' => true, 'message' => 'All data cleared successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error clearing data: ' . $e->getMessage()];
        }
    }
}
?>
