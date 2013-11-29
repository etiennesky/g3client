jQuery(document).ready(function(){
	var g3p_rowHeight;
	var g3p_areaHeight;
	var g3p_cols;
	var g3p_max;
	var g3p_thumbnails;
	var g3p_data;
	var g3p_node;
	
	jQuery('#gallery3_picker_tree').jstree({
		'json_data' : {
			'ajax': {
				'url': ajaxurl,
				'data': {
					what: 'tree',
					action: 'gallery3proxy'
				}
			}
		},
		'core': { animation: 0, initially_open: ['g3pt_1'] },
		'ui': { initially_select: ['g3pt_1'] },
		'themes': {
			'theme': 'default'
		},
		'plugins': ['themes', 'ui', 'json_data', 'sort']
	});
	
	jQuery('#gallery3_picker_tree').bind('select_node.jstree', function (event, data) {
		var id = data.rslt.obj.attr('id').substr(5);
		selectFolder(id);
	});		
});

function selectFolder(id)
{
    g3p_node = id;
    jQuery('#preview').unbind('scroll');
	jQuery.getJSON(ajaxurl, {what: 'photos', node: id, action: 'gallery3proxy'}, function(data) {
		updateImagePicker(data)
	});
}

function updateImageMeta(data)
{
    //alert(JSON.stringify(data));
	jQuery('#pickerThumbContainer').append('<img src="' + data.thumbnail.url + '" style="width: ' + 
		data.thumbnail.width + 'px; height: ' + data.thumbnail.height + 'px;" />');
	jQuery('#pickerTitle').text(data.title);
	jQuery('#pickerFilename').text(data.filename);
	jQuery('#pickerSize').text(data.size);
	
	var publicSizes = 0;
	var importSizeDropdown;
	for (var item in data.sizeList)
	{
		var size = data.sizeList[item];
		
		
		var checked = '';
		if (publicSizes == 0) { checked = ' checked="checked"'; }
		if (size.public == true) { publicSizes++; }
		else { continue; }
		
		var value = size.width + '|' + size.height + '|' + size.url;
		var html = '<div class="image-size-item"><input type="radio" value="' + value + '" name="image-size" id="image-size-' + item + '"' + checked + '>';
		html += '<label for="image-size-' + item + '">' + size.name + '</label><br/>';
		html += '<label for="image-size-' + item + '" class="help">(' + size.width + ' x ' + size.height + ')</label>';
		html += '</div>';		
		jQuery('#pickerSizeContainer').append(html);
	}	
	
	jQuery('#type').val(data.type);
	jQuery('#node').val(data.node);
	jQuery('#pickerUrl').val(data.url);
	jQuery('#fullUrl').val(data.fullUrl);
	jQuery('#urlButtonGallery').attr('title', data.url);
	jQuery('input[name=pickerTitle]').val(data.title);
	jQuery('input[name=pickerImageAlt]').val(data.title);
	jQuery('input[name=pickerCaption]').val(data.description);
	
	if (publicSizes == 0)
	{
		jQuery('#pickerStatus').html("This image resides in a non-public directory. You must import it into WordPress in order to use it.");
		jQuery('input[name=send]').attr('disabled', true);
	}
	else
	{
		jQuery('tr.pickerCanEmbed').fadeIn(200);
		jQuery('span.pickerCanEmbed').fadeIn(200);
		jQuery('#pickerStatus').html("This image is accessible from the Internet.");	
	}
}

function buttonToField(self, fieldId)
{
	jQuery('#' + fieldId).val(self.getAttribute('title'));
}

function entities(inputText)
{
	return jQuery('<div/>').text(inputText).html();
}

function attr_safe(inputText)
{
	inputText += '';
	inputText = inputText.replace(/\'/g, "\\'");
	inputText = inputText.replace(/\"/g, '\\"');
	return inputText;
}

function generateHtml()
{
    var type = jQuery('input[name=type]').val();
    if (  type != undefined && type == "album" )
        return generateAlbumHtml();
    else
        return generateImageHtml();
}

function generateImageHtml()
{
	var html = '';
	
	var imageValue = jQuery('input:radio[name=image-size]:checked').val();
	var img = imageValue.split('|', 3);
	var link = attr_safe(jQuery('input[name=pickerUrl]').val());
	var fullUrl = attr_safe(jQuery('input[name=fullUrl]').val());
	
	var caption = attr_safe(entities(jQuery('input[name=pickerCaption]').val()));
	var align = jQuery('input:radio[name=align]:checked').val();
		
	if (caption != '') { html += '[caption align="' + align + '" width="' + attr_safe(img[0]) + '" caption="' + caption + '"]'; }
	if (link != '') 
    { 
        html += '<a href="' + link + '" '; 
	    html += 'class="g3client_image" ';
	    html += 'rel="group-g3picker" ';
        if ( fullUrl != '' )
            html += 'data-fullimg-href="' + fullUrl + '"';
        html += '>'; 
    }
	html += '<img src="' + attr_safe(img[2]) + '" ';
	html += 'class="';
	console.log(caption+" / "+align);
	console.log(JSON.stringify(jQuery('input:radio[name=align]')));
	//console.log(jQuery('input:radio[name=align]:checked').val());
	if (caption == '') { html += align; }
	html += '" ';
	html += 'width="' + attr_safe(img[0]) + '" height="' + attr_safe(img[1]) + '" ';
	html += '>';
	if (link != '') { html += '</a>'; }
	if (caption != '') { html += '[/caption]'; }

	// TMP
	//html = '[g3client item=' + jQuery('input[name=node]').val() + ']';

	parent.send_to_editor(html);	
}

function generateAlbumHtml()
{
	var html = '';
	
    html += '[g3client item=' + jQuery('input[name=node]').val() + ']';

	parent.send_to_editor(html);	
}

function updateImagePicker(data)
{

	var counter = 0;
	var itemcount = 0;
	
	var prHtml = '';
	g3p_thumbnails = new Array();
	g3p_data = data;
	prHtml += '<table>';
	
	for (var item in data)
	{
		counter++;
		if (counter == 1) { prHtml += '<tr>'; }
		prHtml += '<td id="g3pp_' + itemcount + '" style="width: 200px; height: 200px; cursor: pointer; text-align: center;" onclick="javascript:fetchImage(g3p_data[' + itemcount + ']);"><\/td>';
		g3p_thumbnails[itemcount] = data[item].thumb;
		if (counter == 3) { prHtml += '<\/tr>'; counter = 0; }
		itemcount++;
	}
	
	if (itemcount == 0)
	{
		prHtml += '<tr><td>No pictures in this folder</td></tr>';
	}
	prHtml += '<\/table>';
	jQuery('#preview').html(prHtml);
	
	g3p_rowHeight = jQuery('#preview tr:first').outerHeight();
	g3p_areaHeight = jQuery('#preview').height();
	g3p_cols = 3;	
	g3p_max = itemcount;
	
	evaluateVisibility();
	
	jQuery('#preview').scroll(function(){
		evaluateVisibility();
	});
}

function evaluateVisibility()
{
	var g3p_offset = jQuery('#preview').scrollTop();
	first = Math.floor(g3p_offset / g3p_rowHeight);
	last = Math.ceil((g3p_areaHeight + g3p_offset) / g3p_rowHeight);
	firstImg = g3p_cols * first;
	lastImg = (g3p_cols * last) - 1;
	
	for (iter = firstImg; (iter <= lastImg && iter < g3p_max); iter++)
	{
		var eltId = '#g3pp_' + iter;
		if (jQuery(eltId).hasClass('turned')) { continue; }
		jQuery(eltId).addClass('turned');
		
		var img = document.createElement('img');
		img.setAttribute('src', g3p_thumbnails[iter]);
		img.style.verticalAlign = 'middle';
		
		jQuery(eltId).empty();
		jQuery(img).hide();
		jQuery(eltId).append(img);
		jQuery(img).fadeIn(400);
	}
}

function fetchImage(data)
{
	window.location = userSettings.url + 'wp-admin/media-upload.php?tab=gallery3_picker&gallery3_picker_type=image&post_id=' + post_id + '&gallery3_picker_id=' + data.id + '&thumb=' + data.thumb;
}

function fetchAlbum()
{
//    alert("fetchAlbum "+g3p_node);
    window.location = userSettings.url + 'wp-admin/media-upload.php?tab=gallery3_picker&gallery3_picker_type=album&post_id=' + post_id + '&gallery3_picker_id=' + g3p_node;
}


function importImage(id)
{
	jQuery.getJSON(
		ajaxurl,
		{
			what: 'fetch', 
			post: post_id, 
			photo: id, 
			action: 'gallery3proxy'
		}, function(data)
		{
			window.location = userSettings.url + 'wp-admin/media-upload.php?tab=gallery3_picker&gallery3_import=true&post_id=' + post_id + '&gallery3_picker_id=' + data.id;
		}
	);
}
