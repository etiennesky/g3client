// based on wpgallery js script

(function() {
	tinymce.create('tinymce.plugins.g3client', {

		init : function(ed, url) {
			var t = this;
            //alert("g3client init");

			t.url = url;
			t.editor = ed;
			t._createButtons();

			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('...');
			ed.addCommand('G3Client', function() {
                return;
				var el = ed.selection.getNode(),
					//gallery = wp.media.gallery,
					frame;

				// Check if the `wp.media.gallery` API exists.
				//if ( typeof wp === 'undefined' || ! wp.media || ! wp.media.gallery )
				//	return;

				// Make sure we've selected a g3client node.
				if ( el.nodeName != 'IMG' || ed.dom.getAttrib(el, 'class').indexOf('g3client') == -1 )
					return;

				//frame = gallery.edit( '[' + ed.dom.getAttrib( el, 'title' ) + ']' );

                alert("g3client addCommand "+el+" / "+ ed.dom.getAttrib( el, 'title' )+" / "+frame);

				frame.state('gallery-edit').on( 'update', function( selection ) {
					var shortcode = gallery.shortcode( selection ).string().slice( 1, -1 );
					ed.dom.setAttrib( el, 'title', shortcode );
				});
                alert("g3client addCommand done");
			});

			ed.onInit.add(function(ed) {
				// iOS6 doesn't show the buttons properly on click, show them on 'touchstart'
				if ( 'ontouchstart' in window ) {
					ed.dom.events.add(ed.getBody(), 'touchstart', function(e){
						var target = e.target;
						if ( target.nodeName == 'IMG' && ed.dom.hasClass(target, 'g3client') ) {
							ed.selection.select(target);
							ed.dom.events.cancel(e);
				            t._hideButtons(ed);
							ed.plugins.wordpress._showButtons(target, 'g3clientbtns');
						}
					});
				}
			});

            // repeat code in wordpress plugin, because hideButtons only hides the WP buttons...
			ed.onBeforeExecCommand.add(function(ed, cmd, ui, val) {
				t._hideButtons(ed);
			});

			ed.onSaveContent.add(function(ed, o) {
				t._hideButtons(ed);
			});

			ed.onMouseDown.add(function(ed, e) {
				if ( e.target.nodeName == 'IMG' && ed.dom.hasClass(e.target, 'g3client') ) {
				    t._hideButtons(ed);
					ed.plugins.wordpress._showButtons(e.target, 'g3clientbtns');
				}
			});

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t._do_g3client(o.content);
			});
            
            ed.onSetContent.add(function(ed, o) {
				o.content = t._do_g3client(o.content);
			});

            ed.onExecCommand.add(function(ed, cmd, ui, val) {
                if( cmd == "mceInsertContent" ) {
                    ed.setContent( t._do_g3client(ed.getContent({format : 'raw'})) );
                }
            });

			ed.onPostProcess.add(function(ed, o) {
				if (o.get)
					o.content = t._get_g3client(o.content);
			});
		},



		_do_g3client : function(co) {
//			return co.replace(/\[g3client([^\]]*)\]/g, function(a,b){
//				return '<img src="'+tinymce.baseURL+'/plugins/g3client/img/g3logo.png" class="g3client mceItem" title="g3client'+tinymce.DOM.encode(b)+'" />';
//			});

			ret = co.replace(/\[g3client([^\]]*)\]/g, function(a,b){

                var img = tinymce.baseURL+'/plugins/mceg3client/img/g3logo.png';
                var html = '<img src="'+img+'" class="g3client mceItem" title="g3client'+tinymce.DOM.encode(b)+'"/>';
                var res = b.trim().split("="); 
                if ( res.indexOf("item") != -1 ) 
                {
                    var item=res[res.indexOf("item") + 1];
                    var html1 = html;
                    html = a;
                    var ok = false;
                    jQuery.getJSON(ajaxurl, {what: 'meta', node: item, action: 'gallery3proxy', async: false }, function(data) {
                        //console.log("got data: "+JSON.stringify(data));
                        if( data.type!=null && data.thumbnail.url != undefined )
                        {
                            ok = true;
                            img = data.thumbnail.url;
                        } 
                    })
                        .done(function() {
                            html2 = '<img src="'+img+'" class="g3client mceItem" title="g3client'+tinymce.DOM.encode(b)+'" />';
                            fullco = tinyMCE.activeEditor.getContent({format : 'raw'});
                            fullco = fullco.replace(html,html2);
                            tinyMCE.activeEditor.setContent(fullco);
                        });
                }
				return html;
			});
            return ret;
		},

		_get_g3client : function(co) {

            function getAttr(s, n) {
				n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
				return n ? tinymce.DOM.decode(n[1]) : '';
			};

			return co.replace(/(?:<p[^>]*>)*(<img[^>]+>)(?:<\/p>)*/g, function(a,im) {
				var cls = getAttr(im, 'class');

				if ( cls.indexOf('g3client') != -1 )
					return '<p>['+tinymce.trim(getAttr(im, 'title'))+']</p>';

				return a;
			});
		},

		_createButtons : function() {
			var t = this, ed = tinymce.activeEditor, DOM = tinymce.DOM, editButton, dellButton, isRetina;

			if ( DOM.get('g3clientbtns') )
				return;
            isRetina = ( window.devicePixelRatio && window.devicePixelRatio > 1 ) || // WebKit, Opera
				( window.matchMedia && window.matchMedia('(min-resolution:130dpi)').matches ); // Firefox, IE10, Opera

			DOM.add(document.body, 'div', {
				id : 'g3clientbtns',
				style : 'display:none;'
			});

			editButton = DOM.add('g3clientbtns', 'img', {
				src : isRetina ? t.url+'/img/edit-2x.png' : t.url+'/img/edit.png',
				id : 'g3client_edit',
				width : '24',
				height : '24',
				title : ed.getLang('wordpress.editgallery')
			});

			tinymce.dom.Event.add(editButton, 'mousedown', function(e) {
                //alert("g3client _createButtons edit!");
				var ed = tinymce.activeEditor;
				ed.wpGalleryBookmark = ed.selection.getBookmark('simple');
				ed.execCommand("G3Client");
				//ed.plugins.wordpress._hideButtons();
				t._hideButtons(ed);
			});

			dellButton = DOM.add('g3clientbtns', 'img', {
				src : isRetina ? t.url+'/img/delete-2x.png' : t.url+'/img/delete.png',
				id : 'g3client_del',
				width : '24',
				height : '24',
				title : ed.getLang('wordpress.delgallery')
			});
            //alert("g3client _createButtons "+ t.url+'/img/delete.png');
            //alert("g3client _createButtons dell: "+JSON.stringify(dellButton));

			tinymce.dom.Event.add(dellButton, 'mousedown', function(e) {
				var ed = tinymce.activeEditor, el = ed.selection.getNode();

				if ( el.nodeName == 'IMG' && ed.dom.hasClass(el, 'g3client') ) {
                //alert("g3client _createButtons delete!");
					ed.dom.remove(el);

					ed.execCommand('mceRepaint');
					ed.dom.events.cancel(e);
				}
                
				//ed.plugins.wordpress._hideButtons();
				t._hideButtons(ed);
			});

		},

		_hideButtons : function(ed) 
        {
			var DOM = tinymce.DOM;
            //alert("hide "+DOM+" / "+ DOM.select('#g3clientbtns')+" / "+DOM.select('#wp_editbtns, #wp_gallerybtns'));
			DOM.hide( DOM.select('#g3clientbtns') );
            ed.plugins.wordpress._hideButtons();
		},

		getInfo : function() {
			return {
				longname : 'Gallery Settings',
				author : 'WordPress',
				authorurl : 'http://wordpress.org',
				infourl : '',
				version : "1.0"
			};
		}
	});

	tinymce.PluginManager.add('g3client', tinymce.plugins.g3client);
})();
