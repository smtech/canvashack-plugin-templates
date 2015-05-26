<?php

header('Content-Type: application/javascript');

require_once('config.inc.php');
require_once(SMCANVASLIB_PATH . '/include/canvas-api.inc.php');
require_once(SMCANVASLIB_PATH . '/include/cache.inc.php');

if (preg_match('|.*/courses/(\d+)$|', $_REQUEST['location'], $matches))
{
	$courseId = $matches[1];
} else {
	exit; // we're not on a course page
}

$templatesHtml = getCache('key', "templates-$courseId", 'data');

if (!$templatesHtml) {
	$api = new CanvasApiProcess(CANVAS_API_URL, CANVAS_API_TOKEN);
	
	/* collect all the of the templates */
	$assignmentTemplates = $api->get("/courses/$courseId/assignments",array(
		'search_term' => TEMPLATE_TAG
	));
	/* filter out unsupported assignment types */
	// TODO support those assignment types!
	foreach($assignmentTemplates as $key=>$assignmentTemplate)
		if (in_array('discussion_topic', $assignmentTemplate['submission_types']) ||
			in_array('external_tool', $assignmentTemplate['submission_types']) ||
			in_array('online_quiz', $assignmentTemplate['submission_types'])) {
			unset($assignmentTemplates[$key]);
	}

	// TODO unmask discussion templating (after faculty meetings)
	$discussionTemplates = array();
/*	$discussionTemplates = $api->get("/courses/$courseId/discussion_topics",array(
		'search_term' => TEMPLATE_TAG
	));
*/
	/* filter out unsupported discussion types */
	foreach($discussionTemplates as $key=>$discussionTemplate) {
		if ($discussionTemplate['assignment_id'] != 0) {
			unset($discussionTemplates[$key]);
		}
	}

	// TODO unmaks page templating (after faculty meetings)
	$pageTemplates = array();	
/*	$pageTemplates = $api->get("/courses/$courseId/pages",array(
		'search_term' => TEMPLATE_TAG
	));
*/

	$templateCount = count($assignmentTemplates) + count($discussionTemplates) + count($pageTemplates);
	$singleTemplate = $templateCount == 1;

	/* build the HTML for the template chooser */
	$templatesHtml = '<form id="stmarks-template-chooser" method="post" action="' . APP_URL . '/template-copy.php"><select id="template_id" name="template_id" onchange="stmarks_rebuildTemplateList();"><option disabled selected>Choose a template</option><option disabled />';
	
	if (count($assignmentTemplates) > 0) {
		$templatesHtml .= '<optgroup label="Assignments">';
		foreach($assignmentTemplates as $assignmentTemplate) {
			$templateName = trim(str_replace(TEMPLATE_TAG, '', $assignmentTemplate['name']));
			$templatesHtml .= '<option value="assignments' . TYPE_SEPARATOR . '/courses/' . $courseId . '/assignments/' . $assignmentTemplate['id'] . '"' . ($singleTemplate ? ' selected' : '') . '>' . $templateName . '</option>';			
		}
		$templatesHtml .= '</optgroup>';
	}
	
	if (count($discussionTemplates) > 0) {
		$templatesHtml .= '<optgroup label="Discussions">';
		foreach($discussionTemplates as $discussionTemplate) {
			$templateName = trim(str_replace(TEMPLATE_TAG, '', $discussionTemplate['title']));
			$templatesHtml .= '<option value="discussion_topics' . TYPE_SEPARATOR . '/courses/' . $courseId . '/discussion_topics/' . $discussionTemplate['id'] . '"' . ($singleTemplate ? ' selected' : '') . '>' . $templateName . '</option>';
		}
		$templatesHtml .= '</optgroup>';
	}

	if (count($pageTemplates) > 0) {
		$templatesHtml .= '<optgroup label="Pages">';
		foreach($pageTemplates as $pageTemplate) {
			$templateName = trim(str_replace(TEMPLATE_TAG, '', $pageTemplate['title']));
			$templatesHtml .= '<option value="pages' . TYPE_SEPARATOR . '/courses/' . $courseId . '/pages/' . $pageTemplate['url'] . '"' . ($singleTemplate ? ' selected' : '') . '>' . $templateName . '</option>';
		}
		$templatesHtml .= '</optgroup>';
		
	}
	
	if ($templateCount == 0) {
		$templatesHtml .= '<option value="help@' . $courseId . '" selected>What are templates?</option>';
	} else {
		$templatesHtml .= '<option disabled /><option value="help@' . $courseId . '">What are templates?</option>';
	}

	$templatesHtml .= '<option disabled /><option value="rebuild@' . $courseId . '">Rebuild Template List</option>';
	$templatesHtml .= '</select><input type="submit" value="Go" />';

	setCache('key', "templates-$courseId", 'data', $templatesHtml);
}

?>
function stmarks_rebuildTemplateList() {
	var templateChooser = document.getElementById('stmarks-template-chooser');
	if (templateChooser.template_id.value == 'rebuild<?= TYPE_SEPARATOR . $courseId ?>') {
		templateChooser.submit();
	} else if (templateChooser.template_id.value=='help<?= TYPE_SEPARATOR . $courseId ?>') {
		templateChooser.submit();
	}
}

function stmarks_addTemplateChooser(courseSecondary) {
	var announcementsUrl = /courses\/\d+\/discussion_topics/;
	var newAnnouncementButton = null;
	var courseOptions = courseSecondary.getElementsByClassName('course-options')[0].children;
	for (var i = 0; i < courseOptions.length; i++) {
		if (announcementsUrl.test(courseOptions[i].href)) {
			newAnnouncementButton = courseOptions[i];
		} 
	}
	if (newAnnouncementButton != null) {
		var courseUrl = /.*\/courses\/(\d+).*/;
		var courseId = document.location.href.match(courseUrl)[1];
		var templatesChooser = document.createElement('div');
		templatesChooser.id = 'stmarks_templates';
		templatesChooser.innerHTML = '<?= $templatesHtml ?>';
		newAnnouncementButton.parentElement.appendChild(templatesChooser);
	}
}

function stmarks_templates() {
	stmarks_waitForDOMById(/courses\/\d+/, 'course_show_secondary', stmarks_addTemplateChooser);
}
