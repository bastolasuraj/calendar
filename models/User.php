<?php
// Enhanced User model with role-based permissions

class User {
    private $db;
    private $collection;

    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_USER = 'user';

    public function __construct() {
        $this->db = Database::getInstance()->getDb();
        $this->collection = $this->db->users;
    }

    // Check if any admin user exists
    public function hasAdminUser() {
        return $this->collection->countDocuments(['role' => ['$in' => [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]]]) > 0;
    }

    // Create a new user with role
    public function register($email, $password, $role = self::ROLE_ADMIN, $name = '') {
        if (empty($email) || empty($password) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Check if user already exists
        if ($this->collection->countDocuments(['email' => $email]) > 0) {
            return false;
        }

        // Validate role
        if (!in_array($role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_USER])) {
            $role = self::ROLE_USER;
        }

        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $result = $this->collection->insertOne([
                'email' => $email,
                'password' => $hashedPassword,
                'name' => $name ?: explode('@', $email)[0],
                'role' => $role,
                'active' => true,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'last_login' => null,
                'permissions' => $this->getDefaultPermissions($role)
            ]);

            return $result->getInsertedCount() > 0;
        } catch (Exception $e) {
            error_log('User registration error: ' . $e->getMessage());
            return false;
        }
    }

    // Attempt to log a user in
    public function login($email, $password) {
        try {
            $user = $this->collection->findOne(['email' => $email, 'active' => true]);

            if ($user && password_verify($password, $user['password'])) {
                // Update last login
                $this->collection->updateOne(
                    ['_id' => $user['_id']],
                    ['$set' => ['last_login' => new MongoDB\BSON\UTCDateTime()]]
                );

                return $user;
            }

            return false;
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            return false;
        }
    }

    // Get user by ID
    public function getUserById($userId) {
        try {
            return $this->collection->findOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);
        } catch (Exception $e) {
            error_log('Get user error: ' . $e->getMessage());
            return null;
        }
    }

    // Get all users with pagination
    public function getAllUsers($page = 1, $limit = 25, $filters = []) {
        try {
            $skip = ($page - 1) * $limit;
            $query = [];

            if (!empty($filters['role'])) {
                $query['role'] = $filters['role'];
            }

            if (!empty($filters['active'])) {
                $query['active'] = $filters['active'] === 'true';
            }

            if (!empty($filters['search'])) {
                $query['$or'] = [
                    ['name' => new MongoDB\BSON\Regex($filters['search'], 'i')],
                    ['email' => new MongoDB\BSON\Regex($filters['search'], 'i')]
                ];
            }

            $options = [
                'sort' => ['created_at' => -1],
                'skip' => $skip,
                'limit' => $limit,
                'projection' => ['password' => 0] // Exclude password from results
            ];

            $users = $this->collection->find($query, $options);
            $total = $this->collection->countDocuments($query);

            return [
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'pages' => ceil($total / $limit)
            ];

        } catch (Exception $e) {
            error_log('Get users error: ' . $e->getMessage());
            return ['users' => [], 'total' => 0, 'page' => 1, 'pages' => 0];
        }
    }

    // Update user
    public function updateUser($userId, $data) {
        try {
            $updateData = [];

            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
            }

            if (isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                // Check if email already exists for another user
                $existing = $this->collection->findOne([
                    'email' => $data['email'],
                    '_id' => ['$ne' => new MongoDB\BSON\ObjectId($userId)]
                ]);

                if ($existing) {
                    return false;
                }

                $updateData['email'] = $data['email'];
            }

            if (isset($data['role']) && in_array($data['role'], [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_USER])) {
                $updateData['role'] = $data['role'];
                $updateData['permissions'] = $this->getDefaultPermissions($data['role']);
            }

            if (isset($data['active'])) {
                $updateData['active'] = (bool)$data['active'];
            }

            if (isset($data['password']) && !empty($data['password'])) {
                $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (empty($updateData)) {
                return false;
            }

            $updateData['updated_at'] = new MongoDB\BSON\UTCDateTime();

            $result = $this->collection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($userId)],
                ['$set' => $updateData]
            );

            return $result->getModifiedCount() > 0;

        } catch (Exception $e) {
            error_log('Update user error: ' . $e->getMessage());
            return false;
        }
    }

    // Delete user
    public function deleteUser($userId) {
        try {
            // Don't allow deletion of the last super admin
            $user = $this->getUserById($userId);
            if ($user && $user['role'] === self::ROLE_SUPER_ADMIN) {
                $superAdminCount = $this->collection->countDocuments(['role' => self::ROLE_SUPER_ADMIN]);
                if ($superAdminCount <= 1) {
                    return false; // Cannot delete last super admin
                }
            }

            $result = $this->collection->deleteOne(['_id' => new MongoDB\BSON\ObjectId($userId)]);
            return $result->getDeletedCount() > 0;

        } catch (Exception $e) {
            error_log('Delete user error: ' . $e->getMessage());
            return false;
        }
    }

    // Check if user has permission
    public function hasPermission($userId, $permission) {
        try {
            $user = $this->getUserById($userId);
            if (!$user || !$user['active']) {
                return false;
            }

            return in_array($permission, $user['permissions'] ?? []);

        } catch (Exception $e) {
            error_log('Permission check error: ' . $e->getMessage());
            return false;
        }
    }

    // Get default permissions for role
    private function getDefaultPermissions($role) {
        $permissions = [
            self::ROLE_SUPER_ADMIN => [
                'view_dashboard',
                'manage_bookings',
                'manage_users',
                'manage_settings',
                'manage_forms',
                'manage_templates',
                'view_analytics',
                'export_data',
                'system_admin' // This permission often grants overarching control
            ],
            self::ROLE_ADMIN => [
                'view_dashboard',
                'manage_bookings',
                'manage_users',
                'manage_settings',
                'manage_forms',
                'manage_templates',
                'view_analytics',
                'export_data'
            ],
            self::ROLE_MANAGER => [
                'view_dashboard',
                'manage_bookings',
                'view_analytics',
                'export_data'
            ],
            self::ROLE_USER => [
                'view_dashboard',
                'view_bookings'
            ]
        ];

        return $permissions[$role] ?? [];
    }

    // Get role display name
    public static function getRoleDisplayName($role) {
        $roles = [
            self::ROLE_SUPER_ADMIN => 'Super Administrator',
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_USER => 'User'
        ];

        return $roles[$role] ?? 'Unknown';
    }

    // Create demo users
    public function createDemoUsers() {
        try {
            // Super Admin
            $this->register('admin@demo.com', 'AdminDemo123!', self::ROLE_SUPER_ADMIN, 'Demo Super Admin');

            // Regular Admin
            $this->register('manager@demo.com', 'ManagerDemo123!', self::ROLE_ADMIN, 'Demo Manager');

            // Manager
            $this->register('staff@demo.com', 'StaffDemo123!', self::ROLE_MANAGER, 'Demo Staff');

            return true;

        } catch (Exception $e) {
            error_log('Demo users creation error: ' . $e->getMessage());
            return false;
        }
    }
}
