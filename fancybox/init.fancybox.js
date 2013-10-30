jQuery(document).ready(function($) {
	$('a.g3client_image').fancybox({
		'hideOnContentClick': true,
		'transitionIn': 'elastic',
		'transitionOut': 'fade',
	});

	// lightbox for random photo widget
	$('a.g3client_widget_random_photo_lightbox').fancybox({
		'hideOnContentClick': true,
		'transitionIn': 'elastic',
		'transitionOut': 'fade',
	});
});
