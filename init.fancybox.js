jQuery(document).ready(function($) {
//$(document).ready(function() {

	var sidebar = null;

	$('a.g3client_image').fancybox({
		'hideOnContentClick': true,
		'transitionIn': 'elastic',
		'transitionOut': 'fade',
	    padding: 5,
		scrolling:'no',
	    fullscreenMargin: [ 10, 400, 40, 20],
		leftRatio:0.75,
		
	    helpers: {
			//title: { type : 'inside' },
			//title: null,
			//overlay: { css: { 'background' : 'rgba(255,255,255,0.95)' } },
			overlay: { css: { 'background' : 'rgba(200,200,200,0.95)' } },
			//buttons: { position: 'top' },
			buttons: { position: 'top'},
			thumbs: { width: 100, height: 100 },
            sidebar : {
                // Wrapper element
                type: 'overlay', // 'inside', 'outside', 'outer', 'over', 'overlay'
                position: 'top', // 'bottom'
				width: 400,
				contents: 'loading...',
				masked: true
            }

	    },

        setSidebarContents: function () {
			var txt = '';
			//txt += "<div id='fb-root'></div>";		
			txt += "<div id='sidebar_contents'>";

			txt += "<h2>Title:  " + ($.fancybox.current.title) + "</h2>";

			txt += "<p>&nbsp;</p>";
			txt += "<h3>Share:</h3><p>&nbsp;</p><div>";
			//txt += '<div class="g3client_social_button"><fb:like href="' + this.href + '" send="true" show_faces="false"  layout="box_count" width="50"  ></fb:like></div>';
			txt += "<div class='g3client_social_button' style='margin-right:10px;'>";
			txt += '<div class="fb-like" data-href="' + this.href + '" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true"></div>';
			//txt += '<iframe src="//www.facebook.com/plugins/like.php?href=' + this.href + 'F&amp;width&amp;layout=button&amp;action=like&amp;show_faces=true&amp;share=true&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:110px; height:21px;" allowTransparency="true"></iframe>';
			txt += "</div>";
			// style='margin-top:-3px;' 
			txt += "<div class='g3client_social_button' ><script type='text/javascript' src='https://apis.google.com/js/plusone.js'></script><div class='g-plusone' data-size='medium' data-annotation='none' data-href='" + this.href + "'></div></div>";
			txt += "<div class='g3client_social_button' style='margin-right:10px' ><script type='text/javascript' src='https://apis.google.com/js/plusone.js'></script><div class='g-plus' data-action='share' data-height='20' data-annotation='none' data-href='" + this.href + "'></div></div>";
			txt += '<div class="g3client_social_button"><a href="http://twitter.com/share" class="twitter-share-button" data-url="' + this.href +'" data-count="none" data-text="Photo" data-via="" ></a><script type="text/javascript" src="//platform.twitter.com/widgets.js"></script></div>';
			txt += "</div>";

			txt += "<p>&nbsp;<br>&nbsp;</p>&nbsp;";
			txt += "<h3>Comments:</h3><p>&nbsp;</p>";
			txt += '<div class="fb-comments" data-href="' + this.href + '" data-width="350" data-num-posts="5" data-colorscheme="light"></div>';
			txt += "</div>";

			this.sidebar = $.fancybox.helpers.sidebar;
			this.sidebar.setContents(txt);	
		},

        beforeShow: function(opts, obj) {
			this.setSidebarContents();
		},

		afterShow: function() {
			var sidebar = this.sidebar;
			FB.XFBML.parse(sidebar.sidebar_content[0], function() { sidebar.showSidebar(); });
		},

		beforeChange: function () {
            this.sidebar.hideSidebar();
		},

		afterChange: function () {
            this.setSidebarContents();
		},

	});

	// lightbox for random photo widget
	$('a.g3client_widget_random_photo_lightbox').fancybox({
		'hideOnContentClick': true,
		'transitionIn': 'elastic',
		'transitionOut': 'fade',
	});
});
