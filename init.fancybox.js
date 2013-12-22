
var parseQueryString = function( queryString ) {
    var params = {}, queries, temp, i, l;
    queries = queryString.split('&');
    for ( i = 0, l = queries.length; i < l; i++ ) {
        temp = queries[i].split('=');
        params[temp[0]] = temp[1];
    }    
    return params;
};

var writeQueryArray = function( queries ) {
    var params = Array(), key;
	for (key in queries) {
		params.push(key + '=' + queries[key]);
	}
	return params.join('&');
}

jQuery(document).ready(function($) {

	var show_sidebar = g3client_display_config['g3_addsocialsharing'] == 'on';

	$('a.g3client_image').fancybox({
		'hideOnContentClick': true,
		'transitionIn': 'elastic',
		'transitionOut': 'fade',
	    padding: 5,
		scrolling:'no',
	    fullscreenMargin: show_sidebar ? [ 10, 400, 40, 20] : [ 10, 10, 40, 20],
		leftRatio: show_sidebar ? 0.75 : 0.5,
		
	    helpers: {
			//title: { type : 'inside' },
			//title: null,
			//overlay: { css: { 'background' : 'rgba(255,255,255,0.95)' } },
			overlay: { css: { 'background' : 'rgba(200,200,200,0.95)' } },
			//buttons: { position: 'top' },
			buttons: { position: 'top'},
			thumbs: { width: 100, height: 100 },
			sidebar : (!show_sidebar) ? null : {
                // Wrapper element
                type: 'overlay', // 'inside', 'outside', 'outer', 'over', 'overlay'
                position: 'top', // 'bottom'
				width: 400,
				contents: 'loading...',
				masked: true
			},       
	    },

		beforeLoad: function() {
			// append link to each element of group (as {source href}+?&showitem=data-g3item)
			$.each($.fancybox.group, function(i, item) {
				var href2 = item.href;
				var el = item.element;
				if ( el.attr('data-g3item') != undefined ) { 
					href2 = $(location).attr('href');
					var i = href2.indexOf('?');
					if(i!=-1) {
						var base = href2.substring( 0, i );
						var queries = parseQueryString(href2.substring( i + 1 ));
						queries['showitem'] = el.attr('data-g3item');
						href2 = base + '?' + writeQueryArray(queries);
					}
					else {
						href2 += '?&showitem=' + el.attr('data-g3item');
					}
				}
				$.extend(item, { link : href2 });
			});
		},


        setSidebarContents: function () {
			var txt = '';
			var link = $.fancybox.group[$.fancybox.current.index].link;
			var href = link != undefined ? link : this.href;
			//href = this.href;

			//txt += "<div id='fb-root'></div>";		
			txt += "<div id='sidebar_contents' class='g3client_content'>";

			//txt += "<h1>Title:  " + ($.fancybox.current.title) + "</h2>";
			txt += "<h2>" + ($.fancybox.current.title) + "</h2>";
			var fullimg = $.fancybox.current.fullimg
			if( fullimg != undefined) {
				//txt += "<p>&nbsp;</p>";
				txt += "<h4>Full Size: <a href='" + fullimg + "' target='_blank'>" + fullimg.match(/[^\/?#]+(?=$|[?#])/) + "</a></h4>";
			}

			txt += "<h4>Link: <a href='" + href + "' target='_blank'>link</a></h4>";

			txt +="<hr>"

			//txt += "<br><h4>Share:</h4>";
			txt += "<div>";
			//txt += '<div class="g3client_social_button"><fb:like href="' + href + '" send="true" show_faces="false"  layout="box_count" width="50"  ></fb:like></div>';
			txt += "<div class='g3client_social_button' style='margin-right:10px;'>";
			txt += '<div class="fb-like" data-href="' + href + '" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true"></div>';
			//txt += '<iframe src="//www.facebook.com/plugins/like.php?href=' + href + 'F&amp;width&amp;layout=button&amp;action=like&amp;show_faces=true&amp;share=true&amp;height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:110px; height:21px;" allowTransparency="true"></iframe>';
			txt += "</div>";
			// style='margin-top:-3px;' 
			txt += "<div class='g3client_social_button' ><script type='text/javascript' src='https://apis.google.com/js/plusone.js'></script><div class='g-plusone' data-size='medium' data-annotation='none' data-href='" + href + "'></div></div>";
			txt += "<div class='g3client_social_button' style='margin-right:10px' ><script type='text/javascript' src='https://apis.google.com/js/plusone.js'></script><div class='g-plus' data-action='share' data-height='20' data-annotation='none' data-href='" + href + "'></div></div>";
			txt += '<div class="g3client_social_button"><a href="http://twitter.com/share" class="twitter-share-button" data-url="' + href +'" data-count="none" data-text="Photo" data-via="" ></a><script type="text/javascript" src="//platform.twitter.com/widgets.js"></script></div>';
			txt += "</div>";

			txt += "<p>&nbsp;</p><h4>Comments:</h4>";
			txt += '<div class="fb-comments" data-href="' + href + '" data-width="350" data-num-posts="5" data-colorscheme="light"></div>';
			txt += "</div>";

			this.sidebar = $.fancybox.helpers.sidebar;
			this.sidebar.setContents(txt);	
		},

        beforeShow: function(opts, obj) {
			if(show_sidebar)
				this.setSidebarContents();
		},

		afterShow: function() {
			if(show_sidebar) {
				var sidebar = this.sidebar;
				FB.XFBML.parse(sidebar.sidebar_content[0], function() { sidebar.showSidebar(); });
			}
		},

		beforeChange: function () {
			if(show_sidebar)
				this.sidebar.hideSidebar();
		},

		afterChange: function () {
			if(show_sidebar)
				this.setSidebarContents();
		},

	});

	// lightbox for random photo widget
	$('a.g3client_widget_random_photo_lightbox').fancybox({
		'hideOnContentClick': true,
		'transitionIn': 'elastic',
		'transitionOut': 'fade',
	});
	
	// show first item that has 'data-showitem' attribute
	var objs = $('a.g3client_image');
	for(var n = 0; n < objs.length; n++) {
		var o=objs.eq(n);
		//if(o.attr('data-showitem')=='1') {
		if(o.data('showitem')=='1') {
			o.trigger('click');
		}
	}

});

