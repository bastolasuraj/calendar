<?php
// Enhanced Admin Controller with comprehensive CMS features

require_once 'services/EmailService.php';

class AdminController {
    private $userModel;
    private $bookingModel;
    private $companySettings;
    private $csrf_token;

    public function __construct() {
        // 1) Ensure session is started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // 2) Generate & store one CSRF token per session
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->csrf_token = $_SESSION['csrf_token'];

        // 3) Your existing models
        $this->userModel      = new User();
        $this->bookingModel   = new Booking();
        $this->companySettings = new CompanySettings();
    }

    public function index() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        // Check permissions
        if (!$this->hasPermission('view_dashboard')) {
            $this->accessDenied();
            return;
        }

        // Get dashboard analytics
        $analytics = $this->bookingModel->getBookingAnalytics(30);
        $recentBookings = $this->bookingModel->getAllBookings(1, 10);

        // Get form analytics
        $formConfig = new FormConfiguration();
        $formAnalytics = $formConfig->getFormAnalytics(30);

        // Get system stats
        $totalUsers = $this->userModel->getAllUsers(1, 1)['total'];

        require 'views/admin/index.php';
    }

    public function updateStatus($accessCode = '', $status = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_bookings')) {
            $this->accessDenied();
            return;
        }

        if (empty($accessCode) || empty($status)) {
            header('Location: ' . BASE_PATH . '/admin?error=missingdata');
            exit;
        }

        if ($this->bookingModel->updateBookingStatus($accessCode, $status)) {
            header('Location: ' . BASE_PATH . '/admin?success=statusupdated');
        } else {
            header('Location: ' . BASE_PATH . '/admin?error=updatefailed');
        }
        exit;
    }

    public function bookings() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_bookings')) {
            $this->accessDenied();
            return;
        }

        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $bookings = $this->bookingModel->getAllBookings($page, 25, $filters);

        require 'views/admin/bookings.php';
    }

    public function exportBookings() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('export_data')) {
            $this->accessDenied();
            return;
        }

        $format = $_GET['format'] ?? 'csv';
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        $data = $this->bookingModel->exportBookings($format, $filters);

        if ($data) {
            $filename = 'bookings_export_' . date('Y-m-d_H-i-s') . '.' . $format;

            header('Content-Type: ' . ($format === 'csv' ? 'text/csv' : 'application/json'));
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo $data;
            exit;
        } else {
            header('Location: ' . BASE_PATH . '/admin/bookings?error=exportfailed');
            exit;
        }
    }

    public function users() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_users')) {
            $this->accessDenied();
            return;
        }

        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'role' => $_GET['role'] ?? '',
            'active' => $_GET['active'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $users = $this->userModel->getAllUsers($page, 25, $filters);

        require 'views/admin/user_management.php';
    }

    public function createUser() {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_users')) {
            $this->accessDenied();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $name = trim($_POST['name']);
            $role = $_POST['role'];

            if ($this->userModel->register($email, $password, $role, $name)) {
                header('Location: ' . BASE_PATH . '/admin/users?success=usercreated');
            } else {
                header('Location: ' . BASE_PATH . '/admin/users?error=createfailed');
            }
        } else {
            // Show create user form
            require 'views/admin/create_user.php';
        }
    }

    public function updateUser($userId = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_users')) {
            $this->accessDenied();
            return;
        }

        if (empty($userId)) {
            header('Location: ' . BASE_PATH . '/admin/users');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'role' => $_POST['role'],
                'active' => isset($_POST['active'])
            ];

            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password'];
            }

            if ($this->userModel->updateUser($userId, $data)) {
                header('Location: ' . BASE_PATH . '/admin/users?success=userupdated');
            } else {
                header('Location: ' . BASE_PATH . '/admin/users?error=updatefailed');
            }
        } else {
            $user = $this->userModel->getUserById($userId);
            if (!$user) {
                header('Location: ' . BASE_PATH . '/admin/users?error=usernotfound');
                exit;
            }
            require 'views/admin/edit_user.php';
        }
    }

    public function deleteUser($userId = '') {
        if (!$this->checkAdminAccess()) {
            return;
        }

        if (!$this->hasPermission('manage_users')) {
            $this->accessDenied();
            return;
        }

        if (empty($userId)) {
            header('Location: ' . BASE_PATH . '/admin/users');
            exit;
        }

        // Prevent self-deletion
        if ($userId === $_SESSION['user_id']) {
            header('Location: ' . BASE_PATH . '/admin/users?error=cannotdeleteyourself');
            exit;
        }

        if ($this->userModel->deleteUser($userId)) {
            header('Location: ' . BASE_PATH . '/admin/users?success=userdeleted');
        } else {
            header('Location: ' . BASE_PATH . '/admin/users?error=deletefailed');
        }
        exit;
    }

    public function login() {
        // If already logged in, go to dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/admin');
            exit;
        }

        // Expose $csrf_token to the view
        $csrf_token = $this->csrf_token;
        require __DIR__ . '/../views/admin/login.php';
    }

    public function register() {
        // If an admin user already exists, send them to login
        if ($this->userModel->hasAdminUser()) {
            header('Location: ' . BASE_PATH . '/admin/login');
            exit;
        }

        // Expose $csrf_token to the view
        $csrf_token = $this->csrf_token;
        require __DIR__ . '/../views/admin/register.php';
    }

    public function store() {
        // Registration POST handler
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 1) CSRF check
            $posted = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'], $posted)) {
                die('Invalid CSRF token');
            }

            // 2) Create the user
            $email    = trim($_POST['email']);
            $password = $_POST['password'];
            $name     = trim($_POST['name'] ?? '');

            if ($this->userModel->register($email, $password, User::ROLE_SUPER_ADMIN, $name)) {
                // Initialize defaults
                $this->companySettings->initializeDefaults();
                (new EmailTemplate())->initializeDefaultTemplates();

                header('Location: ' . BASE_PATH . '/admin/login?registered=1');
                exit;
            } else {
                header('Location: ' . BASE_PATH . '/admin/register?error=1');
                exit;
            }
        }
    }

    public function authenticate() {
        // Login POST handler
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 1) CSRF check
            $posted = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'], $posted)) {
                die('Invalid CSRF token');
            }

            // 2) Credentials check
            $email    = trim($_POST['email']);
            $password = $_POST['password'];
            $user     = $this->userModel->login($email, $password);

            if ($user) {
                // Persist user data in session
                $_SESSION['user_id']          = (string)$user['_id'];
                $_SESSION['user_email']       = $user['email'];
                $_SESSION['user_name']        = $user['name'];
                // Ensure 'permissions' is always an array, even if null/missing from DB record
                $_SESSION['user_role']        = $user['role'];
                $_SESSION['user_permissions'] = $user['permissions'] ?? []; // Ensure this is always an array

                header('Location: ' . BASE_PATH . '/admin');
                exit;
            } else {
                header('Location: ' . BASE_PATH . '/admin/login?error=1');
                exit;
            }
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: ' . BASE_PATH . '/admin/login');
        exit;
    }

    // Helper methods
    private function checkAdminAccess() {
        if (!isset($_SESSION['user_id'])) {
            $user = new User();
            if ($user->hasAdminUser()) {
                header('Location: ' . BASE_PATH . '/admin/login');
            } else {
                header('Location: ' . BASE_PATH . '/admin/register');
            }
            exit;
            return false;
        }
        return true;
    }

    private function hasPermission($permission) {
        // Ensure $_SESSION['user_permissions'] is an array before using in_array
        return is_array($_SESSION['user_permissions']) && in_array($permission, $_SESSION['user_permissions']);
    }

    private function accessDenied() {
        require 'views/templates/header.php';
        echo '<div class="alert alert-danger"><h1>Access Denied</h1><p>You do not have permission to access this resource.</p></div>';
        require 'views/templates/footer.php';
    }
}
?>
