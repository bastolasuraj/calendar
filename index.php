<?php
// Main entry point for the application (Front Controller)

// Start the session for tracking login state
session_start();

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Dynamically determine the base path of the application
// This is crucial for correct routing and redirects in subdirectories
$scriptName = $_SERVER['SCRIPT_NAME'];
$scriptDir = dirname($scriptName);
// If index.php is directly in the web root, $scriptDir will be '/' or '\'.
// We want to remove the trailing '/' if it exists, unless it's the only character.
define('BASE_PATH', ($scriptDir === '/' || $scriptDir === '\\') ? '' : rtrim($scriptDir, '/'));

// Require necessary files
require_once 'config/database.php';
require_once 'config/email.php';
require_once 'models/Booking.php';
require_once 'models/User.php';
require_once 'models/FormConfiguration.php';
require_once 'models/CompanySettings.php';
require_once 'models/EmailTemplate.php';
require_once 'services/EmailService.php';
require_once 'services/FormValidationService.php';

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
        // Call the method, passing any additional URL parts as parameters
        $params = array_slice($urlParts, 2);
        call_user_func_array([$controller, $action], $params);
    } else {
        // Handle 404 - Method not found
        http_response_code(404);
        require 'views/templates/header.php';
        echo '<div class="alert alert-danger"><h1>404 - Page Not Found</h1><p>The requested page could not be found.</p></div>';
        require 'views/templates/footer.php';
    }
} else {
    // Handle 404 - Controller not found
    http_response_code(404);
    require 'views/templates/header.php';
    echo '<div class="alert alert-danger"><h1>404 - Controller Not Found</h1><p>The requested resource could not be found.</p></div>';
    require 'views/templates/footer.php';
}
?>