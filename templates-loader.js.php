<?php

require_once('common.inc.php');

header('Content-Type: application/javascript');

?>
var canvashack = {
	loadTemplates: function() {
		$('body').append('<script src="<?= $pluginMetadata['PLUGIN_URL'] ?>/templates.js.php?location=' + window.location.href) + '"></script>');
	}
};
