<?php
// Demo Data Seeder for populating the system with realistic test data

class DataSeeder {
    private $db;
    private $userModel;
    private $bookingModel;
    private $companySettings;
    private $formConfig;
    private $emailTemplate;

    public function __construct() {
        $this->db = Database::getInstance()->getDb();
        $this->userModel = new User();
        $this->bookingModel = new Booking();
        $this->companySettings = new CompanySettings();
        $this->formConfig = new FormConfiguration();
        $this->emailTemplate = new EmailTemplate();
    }

    // Seed all demo data
    public function seedAll() {
        try {
            echo "Starting demo data seeding...\n";

            // Create database indexes first
            Database::getInstance()->createIndexes();
            echo "✓ Database indexes created\n";

            // Seed demo users
            $this->seedDemoUsers();
            echo "✓ Demo users created\n";

            // Initialize company settings
            $this->seedCompanySettings();
            echo "✓ Company settings initialized\n";

            // Initialize email templates
            $this->seedEmailTemplates();
            echo "✓ Email templates created\n";

            // Create form configurations
            $this->seedFormConfigurations();
            echo "✓ Form configurations created\n";

            // Seed demo bookings
            $this->seedDemoBookings();
            echo "✓ Demo bookings created\n";

            echo "\n🎉 Demo data seeding completed successfully!\n";
            echo "\n📧 Demo Login Credentials:\n";
            echo "Super Admin: admin@demo.com / AdminDemo123!\n";
            echo "Manager: manager@demo.com / ManagerDemo123!\n";
            echo "Staff: staff@demo.com / StaffDemo123!\n";

            return true;

        } catch (Exception $e) {
            echo "❌ Error seeding demo data: " . $e->getMessage() . "\n";
            error_log('Demo data seeding error: ' . $e->getMessage());
            return false;
        }
    }

    // Create demo users with different roles
    private function seedDemoUsers() {
        $demoUsers = [
            [
                'email' => 'admin@demo.com',
                'password' => 'AdminDemo123!',
                'name' => 'Demo Super Admin',
                'role' => User::ROLE_SUPER_ADMIN
            ],
            [
                'email' => 'manager@demo.com',
                'password' => 'ManagerDemo123!',
                'name' => 'Demo Manager',
                'role' => User::ROLE_ADMIN
            ],
            [
                'email' => 'staff@demo.com',
                'password' => 'StaffDemo123!',
                'name' => 'Demo Staff Member',
                'role' => User::ROLE_MANAGER
            ]
        ];

        foreach ($demoUsers as $userData) {
            // Check if user already exists
            if ($this->db->users->countDocuments(['email' => $userData['email']]) == 0) {
                $this->userModel->register(
                    $userData['email'],
                    $userData['password'],
                    $userData['role'],
                    $userData['name']
                );
            }
        }
    }

    // Initialize company settings with TechHub branding
    private function seedCompanySettings() {
        $settings = [
            // Company Information
            'company_name' => 'TechHub Coworking Space',
            'company_email' => 'contact@techhub.local',
            'company_phone' => '+1 (555) 123-4567',
            'company_address' => '123 Tech Street, Innovation City, TC 12345',
            'company_website' => 'https://techhub.local',
            'company_description' => 'A modern coworking space designed for technology professionals, entrepreneurs, and digital nomads. We provide flexible workspace solutions with state-of-the-art facilities.',

            // Branding
            'primary_color' => '#2563eb',
            'secondary_color' => '#64748b',
            'accent_color' => '#16a34a',
            'theme' => 'light',

            // Working Hours
            'working_hours_start' => '08:00',
            'working_hours_end' => '18:00',
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
            'email_footer_text' => 'TechHub Coworking Space | 123 Tech Street | https://techhub.local',

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

        $this->companySettings->setMultiple($settings);
    }

    // Create email templates
    private function seedEmailTemplates() {
        $this->emailTemplate->initializeDefaultTemplates();
    }

    // Create sample form configurations
    private function seedFormConfigurations() {
        // Basic booking form
        $basicForm = [
            [
                'type' => 'text',
                'name' => 'full_name',
                'label' => 'Full Name',
                'required' => true,
                'placeholder' => 'Enter your full name',
                'order' => 1
            ],
            [
                'type' => 'email',
                'name' => 'email',
                'label' => 'Email Address',
                'required' => true,
                'placeholder' => 'Enter your email address',
                'order' => 2
            ],
            [
                'type' => 'phone',
                'name' => 'phone',
                'label' => 'Phone Number',
                'required' => false,
                'placeholder' => '+1 (555) 123-4567',
                'order' => 3
            ],
            [
                'type' => 'select',
                'name' => 'laptop_model',
                'label' => 'Laptop Model',
                'required' => true,
                'options' => [
                    'MacBook Pro 13"',
                    'MacBook Pro 16"',
                    'MacBook Air M2',
                    'Dell XPS 13',
                    'Dell XPS 15',
                    'Lenovo ThinkPad X1',
                    'Surface Laptop',
                    'Other'
                ],
                'order' => 4
            ],
            [
                'type' => 'select',
                'name' => 'department',
                'label' => 'Department',
                'required' => false,
                'options' => [
                    'Engineering',
                    'Design & UX',
                    'Marketing',
                    'Sales',
                    'Operations',
                    'Finance',
                    'HR',
                    'Other'
                ],
                'order' => 5
            ],
            [
                'type' => 'textarea',
                'name' => 'purpose',
                'label' => 'Purpose of Visit',
                'required' => false,
                'placeholder' => 'Please describe the purpose of your visit (optional)',
                'rows' => 3,
                'order' => 6
            ]
        ];

        $this->formConfig->saveConfiguration(
            'TechHub Booking Form',
            $basicForm,
            [
                'submit_button_text' => 'Submit Booking Request',
                'success_message' => 'Your booking request has been submitted successfully! You will receive a confirmation email shortly.',
                'require_approval' => true,
                'allow_updates' => true
            ]
        );

        // Extended form with more fields
        $extendedForm = array_merge($basicForm, [
            [
                'type' => 'select',
                'name' => 'company',
                'label' => 'Company',
                'required' => false,
                'options' => [
                    'TechCorp Inc.',
                    'StartupXYZ',
                    'Design Studios',
                    'Freelancer',
                    'Student',
                    'Other'
                ],
                'order' => 7
            ],
            [
                'type' => 'radio',
                'name' => 'visit_type',
                'label' => 'Type of Visit',
                'required' => true,
                'options' => [
                    'Hot Desk',
                    'Private Office',
                    'Meeting Room',
                    'Event Space'
                ],
                'order' => 8
            ],
            [
                'type' => 'checkbox',
                'name' => 'amenities',
                'label' => 'Required Amenities',
                'required' => false,
                'options' => [
                    'High-speed WiFi',
                    'Printing/Scanning',
                    'Coffee/Tea',
                    'Power Outlets',
                    'Monitor/Display',
                    'Whiteboard'
                ],
                'order' => 9
            ]
        ]);

        $this->formConfig->saveConfiguration(
            'Extended Booking Form',
            $extendedForm,
            [
                'submit_button_text' => 'Book Your Space',
                'success_message' => 'Thank you for choosing TechHub! Your booking request is being processed.',
                'require_approval' => true,
                'allow_updates' => true
            ]
        );
    }

    // Create realistic demo bookings
    private function seedDemoBookings() {
        $demoBookings = [
            [
                'form_data' => [
                    'full_name' => 'Sarah Johnson',
                    'email' => 'sarah.johnson@example.com',
                    'phone' => '+1 (555) 234-5678',
                    'laptop_model' => 'MacBook Pro 13"',
                    'department' => 'Design & UX',
                    'purpose' => 'Working on client project designs and presentations'
                ],
                'booking_datetime' => '+1 day 09:00',
                'status' => 'approved'
            ],
            [
                'form_data' => [
                    'full_name' => 'Michael Chen',
                    'email' => 'michael.chen@techcorp.com',
                    'phone' => '+1 (555) 345-6789',
                    'laptop_model' => 'Dell XPS 15',
                    'department' => 'Engineering',
                    'purpose' => 'Code review session and team collaboration'
                ],
                'booking_datetime' => '+1 day 14:00',
                'status' => 'approved'
            ],
            [
                'form_data' => [
                    'full_name' => 'Emma Rodriguez',
                    'email' => 'emma.rodriguez@startupxyz.com',
                    'phone' => '+1 (555) 456-7890',
                    'laptop_model' => 'MacBook Air M2',
                    'department' => 'Marketing',
                    'purpose' => 'Content creation and social media planning'
                ],
                'booking_datetime' => '+2 days 10:00',
                'status' => 'pending'
            ],
            [
                'form_data' => [
                    'full_name' => 'David Kim',
                    'email' => 'david.kim@freelancer.com',
                    'phone' => '+1 (555) 567-8901',
                    'laptop_model' => 'Lenovo ThinkPad X1',
                    'department' => 'Other',
                    'purpose' => 'Client consultation and proposal writing'
                ],
                'booking_datetime' => '+2 days 15:00',
                'status' => 'approved'
            ],
            [
                'form_data' => [
                    'full_name' => 'Lisa Wang',
                    'email' => 'lisa.wang@designstudios.com',
                    'phone' => '+1 (555) 678-9012',
                    'laptop_model' => 'MacBook Pro 16"',
                    'department' => 'Design & UX',
                    'purpose' => 'UI mockup creation and user testing preparation'
                ],
                'booking_datetime' => '+3 days 11:00',
                'status' => 'pending'
            ],
            [
                'form_data' => [
                    'full_name' => 'James Thompson',
                    'email' => 'james.thompson@operations.com',
                    'phone' => '+1 (555) 789-0123',
                    'laptop_model' => 'Surface Laptop',
                    'department' => 'Operations',
                    'purpose' => 'Process optimization workshop'
                ],
                'booking_datetime' => '+3 days 16:00',
                'status' => 'rejected'
            ],
            [
                'form_data' => [
                    'full_name' => 'Maria Garcia',
                    'email' => 'maria.garcia@sales.com',
                    'phone' => '+1 (555) 890-1234',
                    'laptop_model' => 'Dell XPS 13',
                    'department' => 'Sales',
                    'purpose' => 'Client presentation and contract negotiation'
                ],
                'booking_datetime' => '+4 days 13:00',
                'status' => 'approved'
            ],
            [
                'form_data' => [
                    'full_name' => 'Robert Taylor',
                    'email' => 'robert.taylor@finance.com',
                    'phone' => '+1 (555) 901-2345',
                    'laptop_model' => 'MacBook Pro 13"',
                    'department' => 'Finance',
                    'purpose' => 'Budget analysis and financial reporting'
                ],
                'booking_datetime' => '+5 days 08:00',
                'status' => 'pending'
            ],
            [
                'form_data' => [
                    'full_name' => 'Jennifer Brown',
                    'email' => 'jennifer.brown@hr.com',
                    'phone' => '+1 (555) 012-3456',
                    'laptop_model' => 'MacBook Air M2',
                    'department' => 'HR',
                    'purpose' => 'Recruitment interviews and team building planning'
                ],
                'booking_datetime' => '+5 days 14:00',
                'status' => 'approved'
            ],
            [
                'form_data' => [
                    'full_name' => 'Kevin Anderson',
                    'email' => 'kevin.anderson@student.edu',
                    'phone' => '+1 (555) 123-4567',
                    'laptop_model' => 'Other',
                    'department' => 'Other',
                    'purpose' => 'Thesis research and academic writing'
                ],
                'booking_datetime' => '+6 days 10:00',
                'status' => 'approved'
            ],
            [
                'form_data' => [
                    'full_name' => 'Amanda Wilson',
                    'email' => 'amanda.wilson@consulting.com',
                    'phone' => '+1 (555) 234-5678',
                    'laptop_model' => 'Lenovo ThinkPad X1',
                    'department' => 'Other',
                    'purpose' => 'Strategic consulting session with clients'
                ],
                'booking_datetime' => '+7 days 09:00',
                'status' => 'pending'
            ],
            [
                'form_data' => [
                    'full_name' => 'Daniel Lee',
                    'email' => 'daniel.lee@engineering.com',
                    'phone' => '+1 (555) 345-6789',
                    'laptop_model' => 'Dell XPS 15',
                    'department' => 'Engineering',
                    'purpose' => 'Software architecture review and system design'
                ],
                'booking_datetime' => '+7 days 15:00',
                'status' => 'approved'
            ],
            [
                'form_data' => [
                    'full_name' => 'Rachel Green',
                    'email' => 'rachel.green@marketing.com',
                    'phone' => '+1 (555) 456-7890',
                    'laptop_model' => 'MacBook Pro 16"',
                    'department' => 'Marketing',
                    'purpose' => 'Campaign strategy development and content creation'
                ],
                'booking_datetime' => '+8 days 11:00',
                'status' => 'approved'
            ],
            [
                'form_data' => [
                    'full_name' => 'Christopher Davis',
                    'email' => 'christopher.davis@product.com',
                    'phone' => '+1 (555) 567-8901',
                    'laptop_model' => 'Surface Laptop',
                    'department' => 'Other',
                    'purpose' => 'Product roadmap planning and stakeholder meetings'
                ],
                'booking_datetime' => '+8 days 13:00',
                'status' => 'pending'
            ],
            [
                'form_data' => [
                    'full_name' => 'Ashley Martinez',
                    'email' => 'ashley.martinez@creative.com',
                    'phone' => '+1 (555) 678-9012',
                    'laptop_model' => 'MacBook Pro 13"',
                    'department' => 'Design & UX',
                    'purpose' => 'Creative brainstorming and concept development'
                ],
                'booking_datetime' => '+9 days 10:00',
                'status' => 'approved'
            ]
        ];

        foreach ($demoBookings as $bookingData) {
            try {
                $bookingDateTime = date('Y-m-d H:i:s', strtotime($bookingData['booking_datetime']));

                // Create booking
                $result = $this->bookingModel->createBooking($bookingData['form_data'], $bookingDateTime);

                if ($result['success']) {
                    // Update status if not pending
                    if ($bookingData['status'] !== 'pending') {
                        $this->bookingModel->updateBookingStatus($result['access_code'], $bookingData['status']);
                    }
                }
            } catch (Exception $e) {
                error_log('Error creating demo booking: ' . $e->getMessage());
            }
        }
    }

    // Clear all demo data
    public function clearDemoData() {
        try {
            echo "Clearing demo data...\n";

            // Clear collections
            $this->db->users->deleteMany([]);
            $this->db->bookings->deleteMany([]);
            $this->db->form_configurations->deleteMany([]);
            $this->db->company_settings->deleteMany([]);
            $this->db->email_templates->deleteMany([]);

            echo "✓ Demo data cleared\n";
            return true;

        } catch (Exception $e) {
            echo "❌ Error clearing demo data: " . $e->getMessage() . "\n";
            error_log('Demo data clearing error: ' . $e->getMessage());
            return false;
        }
    }

    // Check if demo data exists
    public function hasDemoData() {
        try {
            return $this->db->users->countDocuments(['email' => 'admin@demo.com']) > 0;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
