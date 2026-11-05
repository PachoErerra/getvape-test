<?php
//http_response_code(503);
//header("Retry-After: 3600"); // Через 1 час снова проверить
//echo "<h1>Сайт тимчасово недоступний</h1><p>Будь ласка, зайдіть пізніше.</p>";
//exit;
//?>

<?php
// Version
define('VERSION', '3.0.3.7');
define('VERSION_CORE', 'ocStore');
define('VERSION_BUILD', '0002');
define('VERSION_LANGPACK', 'UK-EN-RU');

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_errors', 'On');
ini_set('display_startup_errors', 1);

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: ../install/index.php');
	exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');
require_once( '../vendor/autoload.php');

start('admin');