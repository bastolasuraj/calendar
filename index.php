<?php
// Main entry point for the application (Front Controller)
phpinfo();
// Start the session for tracking login state
session_start();

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require Composer's autoloader first and foremost
require_once __DIR__ . '/vendor/autoload.php';

// Define BASE_PATH if your application uses it
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/cal'); // Adjust this based on your web server setup
}

// Load config files
require_once 'config/database.php';
require_once 'config/email.php';

// Load all models and services (if not using PSR-4 namespaces, or if needed by direct instantiation)
require_once 'models/User.php';
require_once 'models/Booking.php';
require_once 'models/FormConfiguration.php';
require_once 'models/CompanySettings.php';
require_once 'models/EmailTemplate.php';
require_once 'services/EmailService.php';
require_once 'services/FormValidationService.php';
require_once 'services/FormRenderer.php';
require_once 'services/ValidationService.php'; // Keep this if still present in your file system

// Basic Routing
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'book';
$url = filter_var($url, FILTER_SANITIZE_URL);
$urlParts = explode('/', $url);

// Determine the controller
$controllerName = !empty($urlParts[0]) ? ucfirst($urlParts[0]) . 'Controller' : 'BookController';
$controllerFile = 'controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;

    // --- Instantiate Core Application Dependencies ---
    // Create these instances ONCE and correctly, as they are now used by Booking/FormValidationService
    // and potentially other models/services' constructors.
    try {
        $db = Database::getInstance()->getDb();

        // Models that now (or always did) get DB internally
        $userModel = new User();
        $formConfig = new FormConfiguration();
        $companySettings = new CompanySettings();
        $emailTemplate = new EmailTemplate();

        // EmailService: its constructor was modified to take dependencies for testing.
        // If you reverted EmailService's constructor to NOT take args, use `new EmailService();`.
        // If it *still* takes args (e.g., $companySettings, $emailTemplate), then pass them.
        // ORIGINAL (before testing changes): `public function __construct()`.
        // If that's the case, use: `$emailService = new EmailService();`
        // Assuming you reverted this one too:
        $emailService = new EmailService(); // This will internally create CompanySettings/EmailTemplate if not already done via DI

        // Booking: its constructor was modified to take $db, $formConfig, $companySettings, $emailService.
        // If you reverted Booking's constructor to NOT take args, use `new Booking();`.
        // If it *still* takes args (which is very likely the cause of 404s if it fails), then pass them.
        // Assuming Booking *still needs* these due to recent changes:
        $bookingModel = new Booking($db, $formConfig, $companySettings, $emailService);

        // FormValidationService: its constructor was modified to take $formConfig, $companySettings, $bookingModel.
        // If you reverted FormValidationService's constructor to NOT take args, use `new FormValidationService();`.
        // If it *still* takes args (likely cause of 404s if it fails), then pass them.
        // Assuming FormValidationService *still needs* these due to recent changes:
        $formValidationService = new FormValidationService($formConfig, $companySettings, $bookingModel);

    } catch (Exception $e) {
        // If any core dependency instantiation fails, it means a fundamental problem.
        // This will often lead to a 404 if the controller can't be created.
        // Log the error for debugging.
        error_log("Failed to instantiate core dependencies: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        http_response_code(500); // Internal Server Error
        require 'views/templates/header.php';
        echo '<div class="alert alert-danger"><h1>System Error</h1><p>A critical system error occurred. Please try again later or contact support.</p></div>';
        require 'views/templates/footer.php';
        exit;
    }


    // --- Instantiate Controllers ---
    // These controllers *themselves* are likely still in their original state
    // (creating their models internally via `new ModelName()`).
    // So, their constructors should be called without arguments.
    $controller = null;
    switch ($controllerName) {
        case 'AdminController':
            $controller = new AdminController();
            break;
        case 'BookController':
            // BookController's constructor was modified for DI.
            // If you reverted it, use `new BookController();`.
            // If you did NOT revert it, and it still takes arguments,
            // you'd need to pass them like:
            // $controller = new BookController($formConfig, $bookingModel, $companySettings, $formValidationService, $emailService);
            // Assuming you reverted BookController to its original constructor, which instantiates internally:
            $controller = new BookController();
            break;
        case 'CompanyController':
            $controller = new CompanyController();
            break;
        case 'EmailController':
            $controller = new EmailController();
            break;
        case 'FormBuilderController':
            $controller = new FormBuilderController();
            break;
        case 'ViewController':
            // ViewController's constructor was modified for DI.
            // If you reverted it, use `new ViewController();`.
            // If you did NOT revert it, and it still takes arguments,
            // you'd need to pass them.
            // Assuming you reverted ViewController to its original constructor:
            $controller = new ViewController();
            break;
        default:
            http_response_code(404);
            require 'views/templates/header.php';
            echo '<div class="alert alert-danger"><h1>404 - Controller Not Found</h1><p>The requested resource could not be found.</p></div>';
            require 'views/templates/footer.php';
            exit;
    }

    if ($controller) {
        $action = isset($urlParts[1]) ? $urlParts[1] : 'index';

        if (method_exists($controller, $action)) {
            $params = array_slice($urlParts, 2);
            call_user_func_array([$controller, $action], $params);
        } else {
            http_response_code(404);
            require 'views/templates/header.php';
            echo '<div class="alert alert-danger"><h1>404 - Page Not Found</h1><p>The requested page could not be found.</p></div>';
            require 'views/templates/footer.php';
        }
    }
} else {
    http_response_code(404);
    require 'views/templates/header.php';
    echo '<div class="alert alert-danger"><h1>404 - Controller Not Found</h1><p>The requested resource could not be found.</p></div>';
    require 'views/templates/footer.php';
}