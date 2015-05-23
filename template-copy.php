<?php

require_once('config.inc.php');
require_once(SMCANVASLIB_PATH . '/include/canvas-api.inc.php');
require_once(SMCANVASLIB_PATH . '/include/cache.inc.php');

list($objectType, $objectId) = explode('@', $_REQUEST['template_id']);
if ($objectType == 'rebuild') {
	resetCache('key', "templates-$objectId");
	header('Location: ' . str_replace('/api/v1', '', CANVAS_API_URL) . "/courses/$objectId"); // FIXME this is a hacky way to get the instance URL, there must be something better in SMCanvasLib that I've forgotten about
	exit;
} elseif ($objectType == 'help') {
	header('Location: ' . HELP_URL);
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
		unset($template['has_overrides']);
		unset($template['all_dates']);
		unset($template['course_id']);
		unset($template['html_url']);
		unset($template['peer_review_count']);
		unset($template['needs_grading_count']);
		unset($template['needs_grading_count_by_section']);
		unset($template['unpublishable']);
		unset($template['locked_for_user']);
		unset($template['lock_info']);
		unset($template['lock_explanation']);

		// TODO Post Grades to SIS fields ignroed
		// TODO AssignmentFreezer fields ignored
		
		// TODO handle quizzes intelligently -- https://github.com/smtech/smcanvas-templates/issues/3
		// TODO handle graded discussions intelligently -- https://github.com/smtech/smcanvas-templates/issues/2
		// TODO handle external tools intelligently -- https://github.com/smtech/smcanvas-templates/issues/1
		// TODO handle rubrics intelligently -- https://github.com/smtech/smcanvas-templates/issues/4
 		
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