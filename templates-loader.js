var canvashack = {
	loadTemplates: function() {
		$('body').append('<script src="' + $("script[src$='canvashack.js']").attr('src').replace('canvashack.js', 'hacks/templates/templates.js.php?location=' + window.location.href) + '"></script>');
	}
};