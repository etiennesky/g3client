jQuery(document).ready(function($) {
//$(document).ready(function() {

	var sidebar = null;

	$('a.g3client_image').fancybox({
		'hideOnContentClick': true,
		'transitionIn': 'elastic',
		'transitionOut': 'fade',
	    padding: 5,
		//margin: [ 200, 60, 200, 60 ],
	    //margin: [ 20, 200, 20, 20],
        fullscreenMargin : [ 50, 0, 150, 0 ],
	    //closeBtn: false,
		
	    helpers: {
			//title: { type : 'inside' },
			//overlay: { css: { 'background' : 'rgba(255,255,255,0.95)' } },
			overlay: { css: { 'background' : 'rgba(200,200,200,0.95)' } },
			//buttons: { position: 'top' },
			buttons: { position: 'top'},
			thumbs: { width: 100, height: 100 }
	    },

		beforeShow: function() {

			if ( this.sidebar == null ) {

				var txt = '';
				txt += "<div>";
				txt += "<div class='g3client_social_button' style='margin-top:-3px; margin-right:-15px;'><script type='text/javascript' src='https://apis.google.com/js/plusone.js'></script><div class='g-plusone' data-size='medium'></div></div>";
				txt += '<div class="g3client_social_button"><a href="http://twitter.com/share" class="twitter-share-button" data-url="' + this.href +'" data-count="horizontal" data-text="Photo" data-via="" ></a><script type="text/javascript" src="//platform.twitter.com/widgets.js"></script></div>';
				txt += "</div>";
				txt += "<div>aaaaaaaaaaaaaaaaaaaaaaa</div>";

				this.sidebar = $("<div/>").addClass('fancybox-sidebar').html(txt);
				this.skin.addClass('g3client-fancybox-skin').append(this.sidebar);
			}
		},

	});

	// lightbox for random photo widget
	$('a.g3client_widget_random_photo_lightbox').fancybox({
		'hideOnContentClick': true,
		'transitionIn': 'elastic',
		'transitionOut': 'fade',
	});
});
