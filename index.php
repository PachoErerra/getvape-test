<?php
//http_response_code(503);
//header("Retry-After: 3600"); // Через 1 час снова проверить
//echo "<h1>Сайт тимчасово недоступний</h1><p>Будь ласка, зайдіть пізніше.</p>";
//exit;
//?>

<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri === '/contact-us/' || $uri === '/contact-us') {
    header('Location: /', true, 301);
    exit;
}

// Version
define('VERSION', '3.0.3.7');
define('VERSION_CORE', 'ocStore');
define('VERSION_BUILD', '0002');
define('VERSION_LANGPACK', 'UK-EN-RU');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: install/index.php');
	exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');
require_once( 'vendor/autoload.php');

start('catalog');
require_once('debug_after_index.php');