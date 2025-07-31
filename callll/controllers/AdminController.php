<?php
// Enhanced Admin Controller with comprehensive CMS features
//var_dump($_SESSION);
require_once 'services/EmailService.php';
require_once 'models/User.php'; // Ensure User model is included
require_once 'models/Booking.php'; // Ensure Booking model is included
require_once 'models/FormConfiguration.php'; // Ensure FormConfiguration model is included
require_once 'models/EmailTemplate.php'; // Ensure EmailTemplate model is included
require_once 'models/CompanySettings.php'; // Ensure CompanySettings model is included

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

        // 3) Initialize models
        $this->userModel = new User();
        $this->bookingModel = new Booking();
        $this->companySettings = new CompanySettings();
    }

    public function index() {
        // Check if user is logged in and has admin access
        if (!$this->checkAdminAccess()) {
            return; // Redirection handled by checkAdminAccess
        }

        // Check if the user has permission to view the dashboard
        if (!$this->hasPermission('view_dashboard')) {
            $this->accessDenied();
            return;
        }

        // Get dashboard analytics data
        $rawAnalytics = $this->bookingModel->getBookingAnalytics(30);

        // Restructure analytics data to match the view's expected format
        $analytics = [
            'total' => $rawAnalytics['total_bookings'] ?? 0, // Total bookings count
            'pending' => 0, // Default for pending bookings
            'approved' => 0, // Default for approved bookings
            'rejected' => 0, // Default for rejected bookings
        ];

        // Populate pending, approved, rejected counts from status_counts
        if (isset($rawAnalytics['status_counts'])) {
            foreach ($rawAnalytics['status_counts'] as $statusCount) {
                if (isset($statusCount['_id'])) {
                    $analytics[$statusCount['_id']] = $statusCount['count'];
                }
            }
        }

        // Get the active form configuration for display on the dashboard
        $formConfig = new FormConfiguration();
        $activeForm = $formConfig->getActiveConfiguration();

        // Get recent bookings for the main table on the dashboard
        // Use pagination parameters if they are present in the URL
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        $bookingsData = $this->bookingModel->getAllBookings($page, 10, $filters); // Fetch 10 recent bookings

        // Get form analytics for the dashboard
        $formAnalytics = $formConfig->getFormAnalytics(30);

        // Get total users count for system statistics
        $totalUsersData = $this->userModel->getAllUsers(1, 1); // Fetch just one to get total count
        $totalUsers = $totalUsersData['total'] ?? 0;

        // Make the user model instance available to the view for permission checks
        $user = $this->userModel;

        // Include the dashboard view
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

        // Update the booking status using the Booking model
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

        // Get pagination and filter parameters
        $page = (int)($_GET['page'] ?? 1);
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // Fetch all bookings with pagination and filters
        $bookingsData = $this->bookingModel->getAllBookings($page, 25, $filters);

        // Make the user model available to the view for permission checks
        $user = $this->userModel;

        // FIX: Removed incorrect include of company-settings.php here.
        require __DIR__ .'/../views/admin/bookings.php'; // Assuming a bookings.php view exists. If not, this is the root of the routing problem.
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

        // Fetch all users with pagination and filters
        $usersData = $this->userModel->getAllUsers($page, 25, $filters);

        // Convert the MongoDB Cursor to a plain PHP array immediately after fetching.
        // This allows multiple iterations and checks.
        $allUsers = $usersData['users']->toArray(); // FIX: Convert cursor to array here

        $totalUsers = $usersData['total'] ?? 0;
        $currentPage = $usersData['page'] ?? 1;
        $totalPages = $usersData['pages'] ?? 1;

        $user = $this->userModel;

        require 'views/admin/users.php';
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
            // Make the current user's role available to the view for role options
            $currentUser = $this->userModel->getUserById($_SESSION['user_id']);
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
            header('Location: '. BASE_PATH . '/admin/users');
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
            // Make the current user's role available to the view for role options
            $currentUser = $this->userModel->getUserById($_SESSION['user_id']);
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
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_PATH . '/admin');
            exit;
        }

        // Expose $csrf_token to the view
        $csrf_token = $this->csrf_token;
        require __DIR__ . '/../views/admin/login.php';
    }

    public function register() {
        // If an admin user already exists, redirect to login page
        if ($this->userModel->hasAdminUser()) {
            header('Location: ' . BASE_PATH . '/admin/login');
            exit;
        }

        // Expose $csrf_token to the view
        $csrf_token = $this->csrf_token;
        require __DIR__ . '/../views/admin/register.php';
    }

    public function store() {
        // Handle registration POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF token validation
            $posted_csrf_token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($this->csrf_token, $posted_csrf_token)) {
                die('Invalid CSRF token'); // Critical security check
            }

            // Extract and sanitize user input
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $name = trim($_POST['first_name'] . ' ' . ($_POST['last_name'] ?? '')); // Combine first and last name

            // Register the new user with Super Admin role
            if ($this->userModel->register($email, $password, User::ROLE_SUPER_ADMIN, $name)) {
                // Initialize default company settings and email templates after first admin registration
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
        // Handle login POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF token validation (add if not already present in login form)
            // $posted_csrf_token = $_POST['csrf_token'] ?? '';
            // if (!hash_equals($this->csrf_token, $posted_csrf_token)) {
            //     die('Invalid CSRF token');
            // }

            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $user = $this->userModel->login($email, $password);

            if ($user) {
                // Set session variables upon successful login
                $_SESSION['user_id'] = (string)$user['_id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'] ?? $user['email']; // Fallback to email if name is not set
                $_SESSION['user_role'] = $user['role'];
                // Ensure 'permissions' is always an array.
                $_SESSION['user_permissions'] = isset($user['permissions']) ? (array)$user['permissions'] : [];

                header('Location: ' . BASE_PATH . '/admin');
                exit;
            } else {
                header('Location: ' . BASE_PATH . '/admin/login?error=1');
                exit;
            }
        }
    }

    public function logout() {
        session_unset(); // Unset all session variables
        session_destroy(); // Destroy the session
        header('Location: ' . BASE_PATH . '/admin/login'); // Redirect to login page
        exit;
    }

    // Helper method to check admin access for restricted pages
    private function checkAdminAccess() {
        // If no user ID in session, redirect to login/register based on existing admin users
        if (!isset($_SESSION['user_id'])) {
            if ($this->userModel->hasAdminUser()) {
                header('Location: ' . BASE_PATH . '/admin/login');
            } else {
                header('Location: ' . BASE_PATH . '/admin/register');
            }
            exit;
        }
        return true;
    }

    // Helper method to check if the logged-in user has a specific permission
    private function hasPermission($permission) {
        // Ensure $_SESSION['user_permissions'] is an array before using in_array
        if (!isset($_SESSION['user_permissions']) || !is_array($_SESSION['user_permissions'])) {
            return false;
        }
        return in_array($permission, $_SESSION['user_permissions']);
    }

    // Helper method to display an access denied message
    private function accessDenied() {
        // Include header and footer for a consistent page layout
        require 'views/templates/header.php';
        echo '<div class="alert alert-danger"><h1>Access Denied</h1><p>You do not have permission to access this resource.</p></div>';
        require 'views/templates/footer.php';
        exit;
    }
}