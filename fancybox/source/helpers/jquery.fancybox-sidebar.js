/*!
 * Sidebar helper for fancyBox
 * version: 1.0.0
 * based on Facebook comments helper for fancyBox https://github.com/kachar/fancyBox/
 * @requires fancyBox v2.0 or later
 *
 * 
 * Usage:
 */
(function ($) {
	"use strict";

	//Shortcut for fancyBox object
	var F = $.fancybox;

    //Add helper object
    F.helpers.sidebar = {
        defaults : {
            type     : 'overlay', // 'inside', 'outside', 'outer', 'over', 'overlay'
            position : 'bottom', // 'top' or 'bottom'
			width    : 100, // default value?
			contents : '',
			masked : false
        },

		sidebar : null,
		sidebar_content : null,
		sidebar_mask : null,
		contents : '',

		beforeShow: function (opts) {
			if( ! this.sidebar )
				this.init(opts);

            if (opts.type == 'overlay') {
				// Increase right margin to fit the sidebar into the viewport
				F.coming.margin[1] += opts.width;                    
			}
		},

		beforeClose: function () {
			if (this.sidebar) {
				this.sidebar.remove();
				this.sidebar_mask.remove();
			}

			this.sidebar  = null;
			this.sidebar_content  = null;
			this.sidebar_mask  = null;
		},

		init: function (opts) {
            var current         = F.current,
                text            = this.contents,
			    target;

            if ($.trim(text) === '') {
				text = '';
			}

            this.sidebar = $('<div class="fancybox-sidebar-block fancybox-sidebar-' + opts.type + '-wrap"></div>');
			this.sidebar_content = $('<div id="fancybox-sidebar-content">' + text + '</div>').appendTo(this.sidebar);
			this.sidebar_mask = $("<div/>").addClass('fancybox-sidebar-block fancybox-sidebar-mask');
			if(!opts.masked) this.sidebar_mask.hide()
			
            // Set the sidebar width
			this.sidebar.css({ width: opts.width });
			this.sidebar_mask.css({ width: opts.width });

            switch (opts.type) {
                case 'inside':
                    target = F.skin;
                break;
                case 'outside':
                    target = F.wrap;
                break;
                case 'outer':
                    target = F.outer;
                break;
                case 'over':
                    target = F.inner;
                break;
                case 'float':
                    target = F.skin;
                    this.sidebar.appendTo('body');
                    if (navigator.userAgent.match(/msie/)) {
                        this.sidebar.width( this.sidebar.width() );
                    }
                break;
                default: // 'overlay'
                    target = F.helpers.overlay.overlay;                
                break;
            }

            this.sidebar[ (opts.position === 'top' ? 'prependTo'  : 'appendTo') ](target);
			this.sidebar_mask[ (opts.position === 'top' ? 'prependTo'  : 'appendTo') ](target);

		},

		setContents: function (contents) {
			this.contents = contents;
			if(this.sidebar!=null) {
				this.sidebar_content.html(contents);
			}
		},

		showSidebar: function() {
			if(this.sidebar_mask!=null) this.sidebar_mask.hide();
		},

		hideSidebar: function() {
			if(this.sidebar_mask!=null) this.sidebar_mask.show();
		},

    };

}(jQuery));
