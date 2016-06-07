<?php

require_once __DIR__ . '/common.inc.php';

if (preg_match('|.*/courses/(\d+)$|', $_REQUEST['location'], $matches))
{
	$courseId = $matches[1];
} else {
	exit; // we're not on a course page
}

$templatesHtml = $cache->getCache($courseId);
if (!$templatesHtml) {

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

	$discussionTemplates = array();
	$discussionTemplates = $api->get("/courses/$courseId/discussion_topics",array(
		'search_term' => TEMPLATE_TAG
	));

	/* filter out unsupported discussion types */
	foreach($discussionTemplates as $key=>$discussionTemplate) {
		if ($discussionTemplate['assignment_id'] != 0) {
			unset($discussionTemplates[$key]);
		}
	}

	$pageTemplates = array();
	$pageTemplates = $api->get("/courses/$courseId/pages",array(
		'search_term' => TEMPLATE_TAG
	));


	$templateCount = count($assignmentTemplates) + count($discussionTemplates) + count($pageTemplates);
	$singleTemplate = $templateCount == 1;

	/* build the HTML for the template chooser */

	if (count($assignmentTemplates) > 0) {
		$templatesHtml .= '<optgroup label="Assignments">';
		foreach($assignmentTemplates as $assignmentTemplate) {
			$templateName = trim(str_replace(TEMPLATE_TAG, '', $assignmentTemplate['name']));
			$templatesHtml .= '<option value="assignments' . TYPE_SEPARATOR . '/courses/' . $courseId . '/assignments/' . $assignmentTemplate['id'] . '"' . ($singleTemplate ? ' selected' : '') . '>' . htmlentities($templateName, ENT_QUOTES) . '</option>';
		}
		$templatesHtml .= '</optgroup>';
	}

	if (count($discussionTemplates) > 0) {
		$templatesHtml .= '<optgroup label="Discussions">';
		foreach($discussionTemplates as $discussionTemplate) {
			$templateName = trim(str_replace(TEMPLATE_TAG, '', $discussionTemplate['title']));
			$templatesHtml .= '<option value="discussion_topics' . TYPE_SEPARATOR . '/courses/' . $courseId . '/discussion_topics/' . $discussionTemplate['id'] . '"' . ($singleTemplate ? ' selected' : '') . '>' . htmlentities($templateName, ENT_QUOTES) . '</option>';
		}
		$templatesHtml .= '</optgroup>';
	}

	if (count($pageTemplates) > 0) {
		$templatesHtml .= '<optgroup label="Pages">';
		foreach($pageTemplates as $pageTemplate) {
			$templateName = trim(str_replace(TEMPLATE_TAG, '', $pageTemplate['title']));
			$templatesHtml .= '<option value="pages' . TYPE_SEPARATOR . '/courses/' . $courseId . '/pages/' . $pageTemplate['url'] . '"' . ($singleTemplate ? ' selected' : '') . '>' . htmlentities($templateName, ENT_QUOTES) . '</option>';
		}
		$templatesHtml .= '</optgroup>';

	}

	if ($templateCount == 0) {
		$templatesHtml .= '<option value="help@' . $courseId . '" selected>What are templates?</option>';
	} else {
		$templatesHtml .= '<option disabled /><option value="help@' . $courseId . '">What are templates?</option>';
	}

	$templatesHtml .= '<option disabled /><option value="rebuild@' . $courseId . '">Rebuild Template List</option>';

	$cache->setCache($courseId, $templatesHtml);
}

?>
var canvashack = {
	selectiveSubmit: function() {
		var choice = $('#smtech_canvashack_plugin_templates_chooser #template_id option:selected').attr('value');
		if (choice == 'rebuild<?= TYPE_SEPARATOR . $courseId ?>') {
			$('#smtech_canvashack_plugin_templates_chooser').submit();
		} else if (choice == 'help<?= TYPE_SEPARATOR . $courseId ?>') {
			$('#smtech_canvashack_plugin_templates_chooser').submit();
		}
	},
	add: function() {
		if ($('#course_show_secondary a[href="/courses/<?= $courseId ?>/analytics"]').length > 0) {
			$('#course_show_secondary .course-options').append('<div id="stmarks_templates"><form class="form-inline" id="smtech_canvashack_plugin_templates_chooser" method="post" action="<?= $pluginMetadata['PLUGIN_URL'] ?>/template-copy.php"><div class="input-group"><span class="input-group-btn"><input class="btn btn-primary" type="submit" value="New" /></span><select class="form-control" id="template_id" name="template_id" onchange="smtech_canvashack_plugin_templates.selectiveSubmit();" style="width: auto;"><option disabled selected>Choose a template</option><option disabled /><?= $templatesHtml ?></select></div></form></div>');
		}
	}
};
