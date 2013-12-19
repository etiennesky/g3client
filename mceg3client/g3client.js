// based on wpgallery js script

// TODO remove this function, creates problems with other scripts...
if (!Array.prototype.remove)
{
    /**
     * Add array.remove() convenience method to remove element from array.
     *
     * @param {Object} elem Element to remove.
     */
    Array.prototype.remove = function(elem) {
        var index = this.indexOf(elem);
         
        if (index !== -1) {
            this.splice(index, 1);
        }       
    };
}
(function() {
	tinymce.create('tinymce.plugins.g3client', {

		init : function(ed, url) {
			var t = this;
			t.nodes = {};
			t.url = url;
			t.editor = ed;
			t._createButtons();
			t.g3config = G3Client_config;

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

				//alert("g3client addCommand "+el+" / "+ ed.dom.getAttrib( el, 'title' )+" / "+frame);

				frame.state('gallery-edit').on( 'update', function( selection ) {
					var shortcode = gallery.shortcode( selection ).string().slice( 1, -1 );
					ed.dom.setAttrib( el, 'title', shortcode );
				});
				//alert("g3client addCommand done");
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
            
			//ed.onSetContent.add(function(ed, o) {
			//	o.content = t._do_g3client(o.content);
			//});

			ed.onExecCommand.add(function(ed, cmd, ui, val) {
				//console.log("command: "+cmd+" - "+ui+" - "+val);
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
			//return co.replace(/\[g3client([^\]]*)\]/g, function(a,b){
			//	return '<img src="'+tinymce.baseURL+'/plugins/mceg3client/img/g3logo.png" class="g3client mceItem" title="g3client'+tinymce.DOM.encode(b)+'" />';
			//});

			var t=this;

			ret = co.replace(/\[g3client([^\]]*)\]/g, function(a,b){

				var attrs = wp.shortcode.attrs(b);
				var img = tinymce.baseURL+'/plugins/mceg3client/img/g3logo.png';
				var classes = 'g3client mceItem';
				if ( attrs.named['class'] != undefined ) classes += ' ' + attrs.named['class'];
				var img_att = ' class="'+classes+'" title="g3client'+tinymce.DOM.encode(b)+'"';
				var html = '<img src="'+img+'"'+img_att+'/>';

				if ( attrs.named.item != undefined ) 
				{
					var item = attrs.named.item;
					html = a; // default return value is original text
					if( item in t.nodes )
					{
						if(t.nodes[item] == "waiting")// if we are waiting for result for this node, return original text
						{
							return html;
						}
						else {
							img = t.nodes[item].entity.thumb_url_public;
							return '<img src="'+img+'"'+img_att+'/>';
						}
					}

					t.nodes[item] = "waiting" // save info that we are waiting for result for this node

					// this should probably go into another function for easier code reading
					//jQuery.getJSON(ajaxurl, {what: 'meta', node: item, action: 'gallery3proxy'}, function(data) {
					var url = t.g3config['restapiurl'] + 'item/' + item; // TODO remove quotes?
					jQuery.ajax({ 
						dataType: "json", data: {}, type: "GET", url: url,
						//success: successFunc, error: errorFunc,				
						beforeSend: function(xhr){
							xhr.setRequestHeader('X-Gallery-Request-Key',t.g3config['restapikey']); },
						success : function(data) {
							if( data.entity!=undefined && data.entity.thumb_url_public != undefined )
							{
								t.nodes[item] = data;
								img = data.entity.thumb_url_public;
								// as this happens asynchronously (probably after calling function has exited) 
								// we have to modify editor content directly
								html2 = '<img src="'+img+'"'+img_att+'/>';
								fullco = tinyMCE.activeEditor.getContent({format : 'raw'});
								fullco = fullco.replace(html,html2);
								tinyMCE.activeEditor.setContent(fullco);
							} 
						},
						error : function(data) { 
							console.log('ajax query of url '+url+' returned error: '+JSON.stringify(data));
						}			
					});
				}
 				return html;
			});

            return ret;
		},

		_shortcode_single : function(code) {
			code = code.trim();
			var i = code.indexOf(' ');
			return new wp.shortcode({ 
				tag: code.substring( 0, i ), 
				attrs: code.substring( i+1, code.length ), 
				type: 'single', content : '' });
		},

		_get_g3client : function(co) {

            function getAttr(s, n) {
				n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
				return n ? tinymce.DOM.decode(n[1]) : '';
			};

			var t = this;

			return co.replace(/(?:<p[^>]*>)*(<img[^>]+>)(?:<\/p>)*/g, function(a,im) {

				var cls = getAttr(im, 'class');
				var title = tinymce.trim(getAttr(im, 'title'));

				if ( cls.indexOf('g3client') != -1 && title.indexOf('g3client') == 0 ) {

					// find which align value to apply
					var align = '';			
					var align_types = [ 'alignright', 'aligncenter', 'alignleft' ];
					jQuery.each(align_types, function( index, value ) {
						if ( cls.indexOf(value) != -1 ) align = value;
					});

					//remove all align* classes, and add new align if present
 					sc = t._shortcode_single(title);
					var classes = new Array();
					if ( sc.attrs.named['class'] != undefined ) {
						classes = sc.attrs.named['class'].split(' ');
						jQuery.each(align_types, function( index, value ) { classes.remove(value); });
					}
					if ( align != '' ) classes.push( align ); 
					if( classes.length>0 ) sc.attrs.named['class'] = classes.join(" ");
					else delete sc.attrs.named['class'];
					if ( sc.attrs.named['class'] == '' ) delete sc.attrs.named['class'];

					return '<p>'+sc.string()+'</p>';
				}

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

			tinymce.dom.Event.add(dellButton, 'mousedown', function(e) {
				var ed = tinymce.activeEditor, el = ed.selection.getNode();

				if ( el.nodeName == 'IMG' && ed.dom.hasClass(el, 'g3client') ) {
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
