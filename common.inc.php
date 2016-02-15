<?php

require_once(__DIR__ . '/../common.inc.php');

if (file_exists('manifest.xml')) {
	$manifest = simplexml_load_string(file_get_contents('manifest.xml'));
}

$pluginMetadata = new Battis\AppMetadata($sql, (string) $manifest->id);

$cache = new Battis\HierarchicalSimpleCache($sql, basename(__DIR__));

define('TEMPLATE_TAG', '[TEMPLATE]');
define('TEMPLATE_COPY', 'COPY');
define('TYPE_SEPARATOR', '@');
define('HELP_URL', 'http://helpdesk.stmarksschool.org/blog/using-canvas-templates/');

?>