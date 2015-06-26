<?php

require_once('vendor/autoload.php');
require_once('config.inc.php');
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

$api = new CanvasPest(CANVAS_API_URL, CANVAS_API_TOKEN);

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
		unset($template['has_submitted_submissions']);
		unset($template['peer_review_count']);
		unset($template['needs_grading_count']);
		unset($template['needs_grading_count_by_section']);
		unset($template['unpublishable']);
		unset($template['locked_for_user']);
		unset($template['lock_info']);
		unset($template['lock_explanation']);

		// TODO Post Grades to SIS fields currently ignored
		
		// TODO AssignmentFreezer fields currently ignored
		
		// TODO handle quizzes intelligently -- https://github.com/smtech/smcanvas-templates/issues/3
		// TODO handle external tools intelligently -- https://github.com/smtech/smcanvas-templates/issues/1
		// TODO handle rubrics intelligently -- https://github.com/smtech/smcanvas-templates/issues/4
 		
		$params = array('assignment' => $template);
		break;
	}
	case 'discussion_topics': {
		$template['title'] = str_replace(TEMPLATE_TAG, TEMPLATE_COPY, $template['title']);

		/* fields that shouldn't really be copied */
		unset($template['id']);
		unset($template['html_url']);
		unset($template['posted_at']);
		unset($template['last_reply_at']);
		unset($template['user_can_see_posts']);
		unset($template['discussion_subentry_count']);
		unset($template['read_state']);
		unset($template['unread_count']);
		unset($template['subscribed']);
		unset($template['subscription_hold']);
		unset($template['published']);
		unset($template['locked_for_user']);
		unset($template['lock_info']);
		unset($template['lock_explanation']);
		unset($template['user_name']);
		unset($template['permissions']);
		unset($template['topic_children']);
		
		// TODO handle graded discussions intelligently -- https://github.com/smtech/smcanvas-templates/issues/2
		unset($template['assignment_id']);
		unset($template['root_topic_id']);
		unset($template['only_graders_can_rate']);
		
		// TODO handle podcasts intelligently
		unset($template['podcast_url']);
		
		// TODO handle file attachments intelligently
		unset($template['attachments']);

		$params = $template;

		break;
	}
	case 'pages': {
		$template['title'] = str_replace(TEMPLATE_TAG, TEMPLATE_COPY, $template['title']);

		/* fields that shouldn't really be copied */
		unset($template['url']);
		unset($template['created_at']);
		unset($template['updated_at']);
		unset($template['last_edited_by']);
		unset($template['front_page']);
		unset($template['locked_for_user']);
		unset($template['lock_info']);
		unset($template['lock_explanation']);
		
		$params = array('wiki_page' => $template);
		break;
	}
}

$newObject = $api->post("/courses/$courseId/$objectType", $params);

/* post-processing */
switch ($objectType) {
	case 'assignments': {
		break;
	}
	case 'discussion_topics': {
		if (isset($template['allow_rating'])) {
			$api->put("/courses/$courseId/$objectType/{$newObject['id']}", array('allow_rating' => $param['allow_rating']));
		}
		break;	
	}
	case 'pages': {
		break;
	}
}

header("Location: {$newObject['html_url']}/edit");
exit;

?>