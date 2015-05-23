<?php

require_once('config.inc.php');
require_once(SMCANVASLIB_PATH . '/include/canvas-api.inc.php');
require_once(SMCANVASLIB_PATH . '/include/cache.inc.php');

list($objectType, $objectId) = explode('@', $_REQUEST['template_id']);
if ($objectType == 'rebuild') {
	resetCache('key', "templates-$objectId");
	header('Location: ' . str_replace('/api/v1', '', CANVAS_API_URL) . "/courses/$objectId"); // FIXME this is a hacky way to get the instance URL, there must be something better in SMCanvasLib that I've forgotten about
	exit;
}

preg_match('|/courses/(\d+)/|', $objectId, $matches);
$courseId = $matches[1];

$api = new CanvasApiProcess(CANVAS_API_URL, CANVAS_API_TOKEN);

$template = $api->get($objectId);

$newObject = null;
switch($objectType) {
	case 'assignments': {
		$template['name'] = str_replace(TEMPLATE_TAG, TEMPLATE_COPY, $template['name']);
				
		
		/* fields that shouldn't really be copied */
		unset($template['id']);
		unset($template['created_at']);
		unset($template['updated_at']);
		unset($template['course_id']);
		unset($template['has_submitted_submissions']);
		unset($template['muted']);
		unset($template['html_url']);
		unset($template['needs_grading_count']);
		unset($template['locked_for_user']);
		unset($template['unpublishable']);
		
		$params = array('assignment' => $template);
		$newObject = $api->post("/courses/$courseId/$objectType", $params);
		break;
	}
	case 'discussion_topics': {
		break;
	}
	case 'pages': {
		break;
	}
}


header("Location: {$newObject['html_url']}/edit");
exit;

?>