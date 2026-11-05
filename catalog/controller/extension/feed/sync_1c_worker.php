<?php
/**
 * CLI worker очереди синка 1С.
 * Cron:
 * * * * * /usr/local/php74/bin/php -f /home/if506393/getvape.com.ua/www/catalog/controller/extension/feed/sync_1c_worker.php
 */
$root = realpath(dirname(__FILE__) . '/../../../../');

if ($root === false || !is_file($root . '/index.php')) {
	fwrite(STDERR, "Document root not found\n");
	exit(1);
}

chdir($root);

if (empty($_SERVER['HTTP_HOST'])) {
	$_SERVER['HTTP_HOST'] = 'getvape.com.ua';
}
if (empty($_SERVER['SERVER_NAME'])) {
	$_SERVER['SERVER_NAME'] = 'getvape.com.ua';
}
if (empty($_SERVER['SERVER_PORT'])) {
	$_SERVER['SERVER_PORT'] = '80';
}
if (empty($_SERVER['REMOTE_ADDR'])) {
	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}
if (empty($_SERVER['REQUEST_METHOD'])) {
	$_SERVER['REQUEST_METHOD'] = 'GET';
}
if (empty($_SERVER['HTTPS'])) {
	$_SERVER['HTTPS'] = 'off';
}

$_GET['route'] = 'extension/feed/update/worker';
$_GET['cli'] = '1';
$_REQUEST['route'] = $_GET['route'];
$_REQUEST['cli'] = '1';
$_SERVER['QUERY_STRING'] = http_build_query($_GET);
$_SERVER['REQUEST_URI'] = '/index.php?' . $_SERVER['QUERY_STRING'];
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require $root . '/index.php';
