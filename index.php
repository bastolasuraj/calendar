<?php
// Main entry point for the application (Front Controller)

// 1. ALWAYS START SESSION FIRST (or very early)
session_start();

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Dynamically determine the base path
$scriptName = $_SERVER['SCRIPT_NAME'];
$scriptDir = dirname($scriptName);
define('BASE_PATH', ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim($scriptDir, '/'));

// 2. REQUIRE ALL NECESSARY CLASS DEFINITIONS HERE
// This is critical to prevent __PHP_Incomplete_Class errors if objects are in session
require_once 'config/database.php';
require_once 'config/email.php';
require_once 'models/Booking.php';
require_once 'models/User.php';
require_once 'models/FormConfiguration.php';
require_once 'models/CompanySettings.php';
require_once 'models/EmailTemplate.php';
require_once 'services/EmailService.php';
require_once 'services/FormValidationService.php';
require_once 'services/FormRenderer.php'; // Ensure all services are included
require_once 'services/ValidationService.php'; // Ensure all services are included


// Basic Routing
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'book';
$url = filter_var($url, FILTER_SANITIZE_URL);
$urlParts = explode('/', $url);

// Determine the controller
$controllerName = !empty($urlParts[0]) ? ucfirst($urlParts[0]) . 'Controller' : 'BookController';
$controllerFile = 'controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    $controller = new $controllerName();

    // Determine the method/action
    $action = isset($urlParts[1]) ? $urlParts[1] : 'index';

    // Check if the method exists in the controller
    if (method_exists($controller, $action)) {
        $params = array_slice($urlParts, 2);
        call_user_func_array([$controller, $action], $params);
    } else {
        http_response_code(404);
        require 'views/templates/header.php';
        echo '<div class="alert alert-danger"><h1>404 - Page Not Found</h1><p>The requested page could not be found.</p></div>';
        require 'views/templates/footer.php';
    }
} else {
    http_response_code(404);
    require 'views/templates/header.php';
    echo '<div class="alert alert-danger"><h1>404 - Controller Not Found</h1><p>The requested resource could not be found.</p></div>';
    require 'views/templates/footer.php';
}
?>
