jQuery(document).ready(function($) {
//$(document).ready(function() {
	$('a.g3client_image').fancybox({
		'hideOnContentClick': true,
		'transitionIn': 'elastic',
		'transitionOut': 'fade',
	    padding: 5,
//	    margin: [ 200, 60, 200, 60 ],
        maxMargin : [ 50, 0, 150, 0 ],
	    //closeBtn: false,

	    helpers: {
		//title: { type : 'inside' },
		//overlay: { css: { 'background' : 'rgba(255,255,255,0.95)' } },
		overlay: { css: { 'background' : 'rgba(200,200,200,0.95)' } },
		//buttons: { position: 'top' },
		buttons: { position: 'top'},
		thumbs: { width: 100, height: 100 }
	    }

	});

	// lightbox for random photo widget
	$('a.g3client_widget_random_photo_lightbox').fancybox({
		'hideOnContentClick': true,
		'transitionIn': 'elastic',
		'transitionOut': 'fade',
	});
});
