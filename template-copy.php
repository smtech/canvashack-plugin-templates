<?php

require_once('config.inc.php');
require_once(SMCANVASLIB_PATH . '/include/canvas-api.inc.php');
require_once(SMCANVASLIB_PATH . '/include/cache.inc.php');

list($objectType, $objectId) = explode('@', $_REQUEST['template_id']);
if ($objectType == 'rebuild') {
	resetCache('key', "templates-$objectId");
	header('Location: ' . SCHOOL_CANVAS_INSTANCE . "/courses/$objectId");
	exit;
}
preg_match('|/courses/(\d+)/|', $objectId, $matches);
$courseId = $matches[1];

$api = new CanvasApiProcess(CANVAS_API_URL, CANVAS_API_TOKEN);

$template = $api->get($objectId);

$newObject = $api->post("/courses/$courseId/$objectType", array(
	'assignment[name]' => "{$template['name']} COPY",

));

header("Location: {$newObject['html_url']}/edit");
exit;

?>