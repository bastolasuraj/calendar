<?php
// Form Configuration model for dynamic form builder

class FormConfiguration {
    private $db;
    private $collection;

    public function __construct() {
        $this->db = Database::getInstance()->getDb();
        $this->collection = $this->db->form_configurations;
    }

    // Get active form configuration
    public function getActiveConfiguration() {
        try {
            $config = $this->collection->findOne(['active' => true], ['sort' => ['version' => -1]]);

            if (!$config) {
                // Create default configuration if none exists
                return $this->createDefaultConfiguration();
            }

            return $config;
        } catch (Exception $e) {
            error_log('Get form config error: ' . $e->getMessage());
            return $this->createDefaultConfiguration();
        }
    }

    // Save form configuration
    public function saveConfiguration($name, $fields, $settings = []) {
        try {
            // Deactivate current active configuration
            $this->collection->updateMany(['active' => true], ['$set' => ['active' => false]]);

            // Get next version number
            $lastVersion = $this->collection->findOne([], ['sort' => ['version' => -1]]);
            $version = ($lastVersion ? $lastVersion['version'] : 0) + 1;

            $config = [
                'name' => $name,
                'version' => $version,
                'fields' => $fields,
                'settings' => array_merge([
                    'submit_button_text' => 'Submit Booking',
                    'success_message' => 'Your booking has been submitted successfully!',
                    'require_approval' => true,
                    'allow_updates' => true
                ], $settings),
                'active' => true,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'created_by' => $_SESSION['user_id'] ?? null
            ];

            $result = $this->collection->insertOne($config);

            if ($result->getInsertedCount() > 0) {
                // Clear form validation cache
                $this->clearValidationCache();
                return $config;
            }

            return false;

        } catch (Exception $e) {
            error_log('Save form config error: ' . $e->getMessage());
            return false;
        }
    }

    // Get all form configurations
    public function getAllConfigurations() {
        try {
            return $this->collection->find([], ['sort' => ['created_at' => -1]]);
        } catch (Exception $e) {
            error_log('Get all configs error: ' . $e->getMessage());
            return [];
        }
    }

    // Get form configuration by ID
    public function getConfigurationById($id) {
        try {
            return $this->collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
        } catch (Exception $e) {
            error_log('Get config by ID error: ' . $e->getMessage());
            return null;
        }
    }

    // Activate configuration
    public function activateConfiguration($id) {
        try {
            // Deactivate all configurations
            $this->collection->updateMany(['active' => true], ['$set' => ['active' => false]]);

            // Activate selected configuration
            $result = $this->collection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($id)],
                ['$set' => ['active' => true]]
            );

            if ($result->getModifiedCount() > 0) {
                $this->clearValidationCache();
                return true;
            }

            return false;

        } catch (Exception $e) {
            error_log('Activate config error: ' . $e->getMessage());
            return false;
        }
    }

    // Delete configuration
    public function deleteConfiguration($id) {
        try {
            $config = $this->getConfigurationById($id);
            if ($config && $config['active']) {
                return false; // Cannot delete active configuration
            }

            $result = $this->collection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
            return $result->getDeletedCount() > 0;

        } catch (Exception $e) {
            error_log('Delete config error: ' . $e->getMessage());
            return false;
        }
    }

    // Get current form version
    public function getCurrentFormVersion() {
        $config = $this->getActiveConfiguration();
        return $config ? $config['version'] : 1;
    }

    // Validate field configuration
    public function validateFieldConfig($field) {
        $requiredFields = ['type', 'name', 'label'];

        foreach ($requiredFields as $required) {
            if (!isset($field[$required]) || empty($field[$required])) {
                return false;
            }
        }

        // Validate field type
        $allowedTypes = ['text', 'email', 'phone', 'number', 'textarea', 'select', 'radio', 'checkbox', 'file', 'date', 'time', 'datetime'];
        if (!in_array($field['type'], $allowedTypes)) {
            return false;
        }

        // Validate field name (alphanumeric and underscore only)
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $field['name'])) {
            return false;
        }

        return true;
    }

    // Get field validation rules
    public function getFieldValidationRules($field) {
        $rules = [];

        // Required validation
        if (!empty($field['required'])) {
            $rules['required'] = true;
        }

        // Type-specific validation
        switch ($field['type']) {
            case 'email':
                $rules['email'] = true;
                break;

            case 'phone':
                $rules['phone'] = true;
                break;

            case 'number':
                if (isset($field['min'])) {
                    $rules['min'] = $field['min'];
                }
                if (isset($field['max'])) {
                    $rules['max'] = $field['max'];
                }
                break;

            case 'text':
            case 'textarea':
                if (isset($field['min_length'])) {
                    $rules['min_length'] = $field['min_length'];
                }
                if (isset($field['max_length'])) {
                    $rules['max_length'] = $field['max_length'];
                }
                if (isset($field['pattern'])) {
                    $rules['pattern'] = $field['pattern'];
                }
                break;

            case 'file':
                if (isset($field['allowed_types'])) {
                    $rules['allowed_types'] = $field['allowed_types'];
                }
                if (isset($field['max_size'])) {
                    $rules['max_size'] = $field['max_size'];
                }
                break;
        }

        return $rules;
    }

    // Clear validation cache
    private function clearValidationCache() {
        // Clear any cached validation rules
        if (isset($_SESSION['form_validation_cache'])) {
            unset($_SESSION['form_validation_cache']);
        }
    }

    // Create default form configuration
    private function createDefaultConfiguration() {
        $defaultFields = [
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
                'placeholder' => 'Enter your phone number',
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
                    'MacBook Air',
                    'Dell XPS 13',
                    'Dell XPS 15',
                    'Lenovo ThinkPad',
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
                    'Design',
                    'Marketing',
                    'Sales',
                    'Operations',
                    'Other'
                ],
                'order' => 5
            ],
            [
                'type' => 'textarea',
                'name' => 'purpose',
                'label' => 'Purpose of Visit',
                'required' => false,
                'placeholder' => 'Please describe the purpose of your visit',
                'order' => 6
            ]
        ];

        $defaultConfig = [
            'name' => 'Default Booking Form',
            'version' => 1,
            'fields' => $defaultFields,
            'settings' => [
                'submit_button_text' => 'Submit Booking Request',
                'success_message' => 'Your booking request has been submitted successfully! You will receive a confirmation email shortly.',
                'require_approval' => true,
                'allow_updates' => true
            ],
            'active' => true,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'created_by' => 'system'
        ];

        try {
            $this->collection->insertOne($defaultConfig);
            return $defaultConfig;
        } catch (Exception $e) {
            error_log('Create default config error: ' . $e->getMessage());
            return null;
        }
    }

    // Get form analytics
    public function getFormAnalytics($days = 30) {
        try {
            $startDate = new MongoDB\BSON\UTCDateTime((time() - ($days * 24 * 60 * 60)) * 1000);

            // Get booking collection for analytics
            $bookings = $this->db->bookings;

            // Field usage analytics
            $pipeline = [
                ['$match' => ['created_at' => ['$gte' => $startDate]]],
                ['$unwind' => '$form_data'],
                [
                    '$group' => [
                        '_id' => '$form_data.field_name',
                        'usage_count' => ['$sum' => 1],
                        'completion_rate' => [
                            '$avg' => [
                                '$cond' => [
                                    ['$ne' => ['$form_data.value', '']],
                                    1,
                                    0
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $fieldAnalytics = $bookings->aggregate($pipeline)->toArray();

            return [
                'field_analytics' => $fieldAnalytics,
                'total_submissions' => $bookings->countDocuments(['created_at' => ['$gte' => $startDate]])
            ];

        } catch (Exception $e) {
            error_log('Form analytics error: ' . $e->getMessage());
            return ['field_analytics' => [], 'total_submissions' => 0];
        }
    }
}
?>
