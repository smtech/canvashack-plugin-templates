<?php

define('TOOL_NAME', 'Templating Service');
define('CACHE_DURATION', 21600); // 6 hours
define('TEMPLATE_TAG', '[TEMPLATE]');
define('TYPE_SEPARATOR', '@');

if (!defined('CANVAS_API_URL')) {
	require_once(__DIR__ . '/.ignore.templates-authentication.inc.php');
}

if (!defined('SMCANVASLIB_PATH')) {
	require_once(__DIR__ . '/smcanvaslib/config.inc.php');
}

?>