<?php
/**
 * @package Gallery3_Picker
 * @version 0.9.5
 */

/*
Plugin Name: Gallery3 Picker
Plugin URI: http://wiki.sverok.se/gallery3
Description: Allows users to pick images from a Gallery3 installation in the media browser.
Author: Kriss Andsten
Version: 0.9.5
Author URI: mailto:kriss@sverok.se
*/

require_once( ABSPATH . WPINC . '/registration.php');
require_once( 'proxy.php' );

/* Admin page includes */
add_filter('media_upload_tabs', array('gallery3Picker', 'tab_gallery'));
add_filter('media_upload_gallery3_picker', array('gallery3Picker', 'tab_select_gallery'));
add_filter('media_upload_type_gallery3_form', array('gallery3Picker', 'media_upload_type_gallery3_form'));
//this is called called from g3client.php
//add_action('admin_menu', array('gallery3Picker', 'add_gallery3_picker_menu'));

/* Javascript support */
add_action('wp_ajax_gallery3proxy', array('gallery3Proxy', 'requestHandler'));


class gallery3Picker {	
	function add_gallery3_picker_menu()
	{
        //add_options_page( __( 'Gallery3 settings' ), __( 'Gallery3 media picker' ), 8, basename(__FILE__), array('gallery3Picker', 'gallery3_picker_options_page'));
        add_options_page( __( 'Gallery3 settings' ), __( 'G3client media picker' ), 8, basename(__FILE__), array('gallery3Picker', 'gallery3_picker_options_page'));
	}
	
	function tab_select_gallery()
	{
		$options = get_option('gallery3_picker_options');
		$plugin_url = get_settings('siteurl').'/wp-content/plugins/'.dirname(plugin_basename(__FILE__));
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery.jstree.strip.min', $plugin_url . '/jquery.jstree.min.js');
		wp_enqueue_script('gallery3_picker', $plugin_url . '/gallery.js');
		
		
		if ($options['gallery3_url'] == '' || $options['gallery3_status'] == 'failed')
		{
			return wp_iframe( array('gallery3Picker', 'media_upload_type_gallery3_config_error'));
		}
		else if ($_GET['gallery3_picker_id'] && $_GET['deleted'] != 1 )
		{
            if ( $_GET['gallery3_picker_type'] && $_GET['gallery3_picker_type']=='album' ) {
				return wp_iframe( array('gallery3Picker', 'media_upload_type_gallery3_insert_form'), 'gallery3', $errors, $id );                
            }
            else {
			if ($_GET['gallery3_import'] == 'true')
			{
				return wp_iframe( array('gallery3Picker', 'media_upload_type_gallery3_native_insert_form'), 'gallery3', $errors, $id );
			}
			else
			{
				return wp_iframe( array('gallery3Picker', 'media_upload_type_gallery3_insert_form'), 'gallery3', $errors, $id );				
			}
            }
		}
		else
		{
			return wp_iframe( array('gallery3Picker', 'media_upload_type_gallery3_form'), 'gallery3', $errors, $id );
		}
	}
	
	function media_upload_type_gallery3_insert_form($type = 'file', $errors = null, $id = null) {
		$node_id = isset( $_GET['gallery3_picker_id'] )? intval( $_GET['gallery3_picker_id'] ) : 0;
		$post_id = isset( $_REQUEST['post_id'] )? intval( $_REQUEST['post_id'] ) : 0;
		$back = admin_url("media-upload.php?tab=gallery3_picker&post_id=$post_id");
		$thumbUrl = $_REQUEST['thumb'];
		$permaLink = get_permalink( $post_id );
		media_upload_header();
				
echo <<<EOT
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery.getJSON(ajaxurl, 
					{
						what: 'meta', 
						node: $node_id, 
						action: 'gallery3proxy'
					}, function(data) {
						updateImageMeta(data);
					});
			});
		</script>


		<form enctype="multipart/form-data" method="post" class="media-upload-form type-form validate" id="$type-form">
		<div id="pickerNav">
			<a href="$back">Return to gallery</a>
		</div>
		
		<div id="pickerThumb" class="describe">
			<table>
				<tbody>
				<tr style="height: 230px;">
					<td colspan="2">
						<table>
							<tr>
								<td id="pickerThumbContainer" style="width: 220px;">
									
								</td>
								<td valign="top">
									<p>
										<span id="pickerTitle"></span>
									</p>
									<p>
										<span id="pickerFilename"></span><br />
									</p>
									<p>
										<span id="pickerSize"></span>
									</p>
                                    <p>&nbsp;</p>
					                <p><span class='savesend pickerCanEmbed'>
						                <input type='button' class='button' name='send' onclick="generateHtml();" value='Insert into Post' /> 
						                <a href='#' class='del-link' onclick="parent.eval('tb_remove()'); return false;">Cancel</a>
					                </span></p>
									<input type="button" onclick="importImage($node_id);" class="button" value="Import into WordPress" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr class="status">
					<td colspan="2"><span id="pickerStatus">Loading image data...</span></td>
				</tr>
				<tr class='post_title pickerCanEmbed'  style="display: none;">
					<th valign='top' scope='row' class='label'><label for='pickerTitle'><span class='alignleft'>Title</span><span class="alignright"></span><br class='clear' /></label></th>
					<td class='field'><input type='text' class='text' id='pickerTitle' name='pickerTitle' value='' /></td>
				</tr>
				<tr class='image_alt pickerCanEmbed'  style="display: none;">
					<th valign='top' scope='row' class='label'><label for='pickerImageAlt'><span class='alignleft'>Alternate Text</span><br class='clear' /></label></th>
					<td class='field'><input type='text' class='text' id='pickerImageAlt' name='pickerImageAlt' value=''  /><p class='help'>Alt text for the image, e.g. &#8220;The Mona Lisa&#8221;</p></td>
				</tr>
				<tr class='post_excerpt pickerCanEmbed'  style="display: none;">
					<th valign='top' scope='row' class='label'><label for='pickerCaption'><span class='alignleft'>Caption</span><br class='clear' /></label></th>
					<td class='field'><input type='text' class='text' id='pickerCaption' name='pickerCaption' value=''  /></td>
				</tr>
				<tr class="url pickerCanEmbed" style="display: none;">
					<th valign='top' scope='row' class='label'>
						<label for='pickerUrl'>
							<span class='alignleft'>Link URL</span><br class='clear' />
						</label>
					</th>
					<td class='field'>
						<input type='text' class='text urlfield' name="pickerUrl" id="pickerUrl" value="" /><br />
						<input type='hidden' name="type" id="type" value="" /><br />
						<input type='hidden' name="node" id="node" value="" /><br />
						<input type='hidden' name="fullUrl" id="fullUrl" value="" /><br />
						<button type='button' id="urlButtonEmpty" class='button urlnone' onclick="buttonToField(this,'pickerUrl');" title="">None</button>
						<button type='button' id="urlButtonGallery" class='button urlfile' onclick="buttonToField(this,'pickerUrl');" title="">Gallery page</button>
						<button type='button' id="urlButtonPost" class='button urlpost' onclick="buttonToField(this,'pickerUrl');" title="$permaLink">Post URL</button>
						<p class='help'>Enter a link URL or click above for presets.</p>
					</td>
				</tr>
				<tr class='align pickerCanEmbed' style="display: none;">
					<th valign='top' scope='row' class='label'>
						<label for='align'>
							<span class='alignleft'>Alignment</span><br class='clear' />
						</label>
					</th>
					<td class='field'>
						<input type='radio' name='align' id='image-align-none' value='alignnone' checked='checked' /><label for='image-align-none' class='align image-align-none-label'>None</label>
						<input type='radio' name='align' id='image-align-left' value='alignleft' /><label for='image-align-left' class='align image-align-left-label'>Left</label>
						<input type='radio' name='align' id='image-align-center' value='aligncenter' /><label for='image-align-center' class='align image-align-center-label'>Center</label>
						<input type='radio' name='align' id='image-align-right' value='alignright' /><label for='image-align-right' class='align image-align-right-label'>Right</label>
					</td>
				</tr>
				<tr class="image-size pickerCanEmbed" style="display: none;">
					<th valign='top' scope='row' class='label'>
						<label for='align'>
							<span class='alignleft'>Size</span><br class='clear' />
						</label>
					</th>
					<td class="field" id="pickerSizeContainer">
						
					</td>
				</tr>
<!--
				<tr class='submit pickerCanEmbed' style="display: none;">
					<td></td>
					<td class='savesend'>
						<input type='button' class='button' name='send' onclick="generateImageHtml();" value='Insert into Post' /> 
						<a href='#' class='del-link' onclick="parent.eval('tb_remove()'); return false;">Cancel</a>
					</td>
				</tr>
-->
				</tbody>
			</table>
		</div>
		
		</form>
EOT;
	}
	
	function media_upload_type_gallery3_native_insert_form($type = 'file', $errors = null, $id = null) {
	
		$post_id = isset( $_REQUEST['post_id'] )? intval( $_REQUEST['post_id'] ) : 0;
		$form_action_url = admin_url("media-upload.php?type=file&tab=type&post_id=$post_id");
		$form_action_url = apply_filters('media_upload_form_url', $form_action_url, $type);
		
		media_upload_header();
		?>
		<form enctype="multipart/form-data" method="post" action="<?php echo esc_attr($form_action_url); ?>" class="media-upload-form type-form validate" id="<?php echo $type; ?>-form">
		<input type="submit" class="hidden" name="save" value="" />
		<input type="hidden" name="post_id" id="post_id" value="<?php echo (int) $post_id; ?>" />
		<?php wp_nonce_field('media-form'); ?>
		
		<script type="text/javascript">
		jQuery(function($){
			var preloaded = $(".media-item.preloaded");
			if ( preloaded.length > 0 ) {
				preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
			}
			updateMediaForm();
		});
		</script>
		<div id="media-items">
			<? echo get_media_items( $_GET['gallery3_picker_id'], $errors ); ?>
		</div>
		<p>
		<input type="submit" class="button savebutton hide-if-no-js" name="save" value="<?php esc_attr_e( 'Save all changes' ); ?>" />
		</p>
		</form>
		<?
	}
	
	function media_upload_type_gallery3_config_error()
	{
		$options = get_option('gallery3_picker_options');
		media_upload_header();
		
		$config_url = site_url('wp-admin/', 'admin');
		$config_url .= "options-general.php?page=" . basename(__FILE__);
		$error = $options['gallery3_status_message'];
		if ($error == '') { $error = _('No configuration found'); }
		
		?>
			<form class="media-upload-form type-form">
			<h3 class="media-title"><?php echo _('Module not configured'); ?></h3>
			<div>
				Sorry, but your Gallery3 module configuration doesn't seem to work:<br />
				<? echo $error ?><br />
				<? echo _('Please go'); ?>
				<a target="_parent" href="<? echo $config_url?>"><? echo _('here') ?></a> 
				<? echo _('to configure'); ?>
			</div>
			</form>
		<?
	}

	
	function media_upload_type_gallery3_form($type = 'file', $errors = null, $id = null) {
		media_upload_header();
			
		?>
		<form enctype="multipart/form-data" method="post" action="<?php echo esc_attr($form_action_url); ?>" class="media-upload-form type-form validate" id="<?php echo $type; ?>-form">
		<input type="submit" class="hidden" name="save" value="" />
		<input type="hidden" name="post_id" id="post_id" value="<?php echo (int) $post_id; ?>" />
		<?php wp_nonce_field('media-form'); ?>
			
		<h3 class="media-title"><?php _e('Add media files from '); ?><?php echo $options['gallery3_name']; ?></h3>
		<div id="gallery3_picker_tree" style="margin-right: 20px;">
				<ul>
					<li id="g3pt_0"><span id="gallery3_picker_tree_base">Loading...</span></li>
				</ul>
		</div>
        <div>
          <input type="button" value="Add Album" onClick="javascript:fetchAlbum();" />
		</div>
		<div id="media-items">
			<div class="media-item media-blank" id="gallery3_picker_preview" style="height: 500px;">
				<div id="preview" style="height: 500px; overflow: auto;"><table><tr><td>No pictures in this folder</td></tr></table></div>
			</div>
		</div>	
		<?
	}
	
	function tab_gallery($tabs)
	{
		$options = get_option('gallery3_picker_options');
		$tabs['gallery3_picker'] = $options['gallery3_name'];
		return $tabs;
	}
	
	
	
	
/*

	Configuration 

*/
	
	/*
		This function is more complicated than one would like, but there's a 
		few oddities we need to cope with.
		
		mod_rewrite: Sometimes <base>/index.php/rest/item/1 is OK, sometimes it 
		gets redirected to <base>/rest/item/1, depending on the host. I didn't 
		bother looking into the details, since I wish to retain my sanity.
		
		CURLOPT_FOLLOWLOCATION: PHP with open_basedir set won't work with this 
		curl option, so we need to figure out the proper path from the get-go.
	*/
	function gallery3_connection_test()
	{
		$options = get_option('gallery3_picker_options');
		
		/* Ensure that we've got an URL ending in / for greater sanity.. */
		if (substr($options['gallery3_config_url'], -1, 1) != '/') {
			$options['gallery3_config_url'] .= '/';
		}
		
		$url = $options['gallery3_config_url'];
		
		/* First off, attempt to use the non-mod_rewrite URL */
		$candidate = $url;
		if (substr($candidate, -10, 10) != 'index.php/') { $candidate .= 'index.php/'; };
		
		$http_status = gallery3Picker::rest_test($candidate);
		
		$message = '';
		$status = 'failed';
		
		/*
			If we get a redirect here, chances are that we are using mod_rewrite
			on the gallery side of things. New request, see what happens.
		*/
		if ($http_status == 302)
		{
			$http_status = gallery3Picker::rest_test($url);
			switch ($http_status)
			{
				case 200:
					$options['gallery3_status'] = 'ok';
					$options['gallery3_status_message'] = 'Configration OK, mod_rewrite support detected.';
					$options['gallery3_url'] = $url;
					break;
					
				case 302:
					$options['gallery3_status'] = 'failed';
					$options['gallery3_status_message'] = "Got second redirect, which I can't handle.";
					$options['gallery3_url'] = $options['gallery3_config_url'];
					break;

				case 403:
					$options['gallery3_status'] = 'failed';
					$options['gallery3_status_message'] = 'Gallery3 REST API key incorrect.';
					$options['gallery3_url'] = $options['gallery3_config_url'];
					break;
				
				case 500:
					$options['gallery3_status'] = 'failed';
					$options['gallery3_status_message'] = "Internal Server Error, cannot verify configuration.";
					$options['gallery3_url'] = $options['gallery3_config_url'];
					break;
			}
		}
		else
		{
			switch ($http_status)
			{
				case 200:
					$options['gallery3_status'] = 'ok';
					$options['gallery3_status_message'] = 'Configration OK, no mod_rewrite support.';
					$options['gallery3_url'] = $candidate;
					break;
				
				case 302:
					$options['gallery3_status'] = 'failed';
					$options['gallery3_status_message'] = "Got second redirect, which I can't handle.";
					$options['gallery3_url'] = $options['gallery3_config_url'];
					break;
				
				case 403:
					$options['gallery3_status'] = 'failed';
					$options['gallery3_status_message'] = 'Gallery3 REST API key incorrect.';
					$options['gallery3_url'] = $options['gallery3_config_url'];
					break;

				case 500:
					$options['gallery3_status'] = 'failed';
					$options['gallery3_status_message'] = "Internal Server Error, cannot verify configuration.";
					$options['gallery3_url'] = $options['gallery3_config_url'];
					break;
			}
		}
		
		$maxlength = 4000;		
		update_option('gallery3_picker_options', $options);
		
		if ($options['gallery3_status'] == 'ok')
		{
			$options['gallery3_query_type'] = gallery3Picker::registry();
			
			if ($options['gallery3_query_type'] == '3.0.0')
			{
				$options['gallery3_maxlength'] = gallery3Picker::suhosin_test();
			}
			
			update_option('gallery3_picker_options', $options);
		}		
	}
	
	function apiCheck($req)
	{
		$options = get_option('gallery3_picker_options');
		if ($options['gallery3_api_key'] != '')
		{
			curl_setopt($req, CURLOPT_HTTPHEADER,array(
				'X-Gallery-Request-Key: ' . $options['gallery3_api_key']
			));
		}
		
		return true;
	}
	
	function rest_test($url)
	{
		$options = get_option('gallery3_picker_options');
		
		$req = curl_init($url . 'rest/item/1');
		curl_setopt($req, CURLOPT_RETURNTRANSFER, true);		
		gallery3Picker::apiCheck($req);
		$response = curl_exec($req);
		$status = curl_getinfo($req, CURLINFO_HTTP_CODE);
		curl_close($req);
		
		return $status;
	}
	
	function registry()
	{
		$options = get_option('gallery3_picker_options');
		$url = $options['gallery3_url'];
		
		$req = curl_init($url . 'rest/registry/');
		curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
		gallery3Picker::apiCheck($req);
		$response = curl_exec($req);
		$status = curl_getinfo($req, CURLINFO_HTTP_CODE);
		curl_close($req);
		
		$data = json_decode($response);
		if (in_array('tree', $data)) { return 'tree'; } 
		return '3.0.0';
	}
	
	function suhosin_test()
	{
		$options = get_option('gallery3_picker_options');
		$url = $options['gallery3_url'];
		
		$baseQuery = $url . 'rest/items?urls=[';
		$baseItemQuery = '"' . $url . 'rest/item/1"';
		$itemLength = strlen($baseItemQuery) + 1; // Account for the comma after the item
		$baseLength = 7; // strlen('urls=[]');
		$acceptedLength = 0;
		
		/*
			Try some different request lengths. Note that we'll be shooting for
			two different limits here:
			1. A global Request URI limit, imposed by the server.
			2. A per-value length limit, imposed by suhosin. 
			
			The lengths in the array specify the limit for #2, so we leave a little
			headroom to account for the overhead of the URL itself and other GET
			vars.
		*/
		
		/*
			We could probably get away with 8000, but it seems that G3 will hit
			some standard memory limit (32MB?) if we do this..
		*/
		$lengths = array(
			4000,	// Try half that
			1980,	// Half'ish that
			980,
			512		// Suhosin suhosin.get.max_value_length default.
		);
		foreach($lengths as $target)
		{
			$fits = floor(($target - $baseLength) / $itemLength);
			$filler = array();
			for ($i = 1; $i <= $fits; $i++)
			{
				$filler[] = $baseItemQuery;
			}
			
			$requestUrl = $baseQuery . join(',', $filler) . ']';
			$req = curl_init($requestUrl);
			curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
			gallery3Picker::apiCheck($req);			
			$response = curl_exec($req);
			$status = curl_getinfo($req, CURLINFO_HTTP_CODE);
			curl_close($req);
			
			if ($status == 200 && $response == '[]') { continue; }
			if ($status == 414) { continue; } // Request-URI Too Long, w3c.
			
			$acceptedLength = $target;
			break;
		}
		
		return $acceptedLength;
	}
	
	function gallery3_picker_options_page()
	{
		// Setup Default Options Array
		$optionarray_def = array(
			'gallery3_config_url' => 'http://bilder.sverok.se/',
			'gallery3_name' => 'Bildbank, Sverok',
			'gallery3_api_key' => '',
			'gallery3_maxlength' => 4000
		);
		
		if (isset($_POST['submit']) ) {		 
			// Options Array Update
			$opts = get_option('gallery3_picker_options');
			$opts['gallery3_config_url'] = $_POST['gallery3_config_url'];
			$opts['gallery3_name'] = $_POST['gallery3_name'];
			$opts['gallery3_api_key'] = $_POST['gallery3_api_key'];
			
			if ($opts['gallery3_name'] == '') { $opts['gallery3_name'] = 'Gallery 3'; }
			update_option('gallery3_picker_options', $opts);
			
			gallery3Picker::gallery3_connection_test();
		}
		
		// Get Options
		$optionarray_def = get_option('gallery3_picker_options');
		
		/* v <0.90 users? */
		if ($optionarray_def['gallery3_config_url'] == '')
		{
			$optionarray_def['gallery3_config_url'] = $optionarray_def['gallery3_url'];
		}
	
		?>
		<div class="wrap">
		<h2>Gallery3 media picker</h2>
		<p>
			The Gallery3 default setting is to disallow guest access for the REST API (which this 
			plugin uses). If you run Gallery3 in this mode, you need an API key.
		</p>
		<p>
			Media from Gallery3 will be pulled into the local WordPress Media Library before use - as 
			such, any media used will - for better or worse - <i>not</i> be deep linked from the Gallery3 
			page, but rather be served from your blog.
		</p>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . basename(__FILE__); ?>&updated=true">
		<?php wp_nonce_field('update-options'); ?>
		<h4><?php _e( 'Gallery3 Settings' ) ?></h4>
		<table width="700px" cellspacing="2" cellpadding="5" class="editform">
			<tr valign="center"> 
				<td width="300px" scope="row"><?php _e( 'Gallery3 URL' ) ?></td> 
				<td><input type="text" name="gallery3_config_url" id="gallery3_config_url_inp" value="<?php echo $optionarray_def['gallery3_config_url']; ?>" size="50" /></td>
			</tr>
			<?php if (
				$optionarray_def['gallery3_url'] != '' && 
				$optionarray_def['gallery3_url'] != $optionarray_def['gallery3_config_url']
			): ?>
			<tr valign="center"> 
				<td width="300px" scope="row"><?php _e( 'Gallery3 REST API' ) ?></td> 
				<td><input type="text" disabled="true" name="gallery3_ro_url" id="gallery3_url_inp" value="<?php echo $optionarray_def['gallery3_url']; ?>" size="50" /></td>
			</tr>			
			<?php endif ?>
			<tr valign="center"> 
				<td width="300px" scope="row"><?php _e( 'Descriptive name' ) ?></td> 
				<td><input type="text" name="gallery3_name" id="gallery3_name_inp" value="<?php echo $optionarray_def['gallery3_name']; ?>" size="50" /></td>
			</tr>
			<tr valign="center"> 
				<td width="300px" scope="row"><?php _e( 'API key (if any)' ) ?></td> 
				<td><input type="text" name="gallery3_api_key" id="gallery3_api_key_inp" value="<?php echo $optionarray_def['gallery3_api_key']; ?>" size="50" /></td>
			</tr>
		</table>
			<div class="submit">
				<span><?php echo $optionarray_def['gallery3_status_message']; ?></span><br />
				<?php if ($optionarray_def['gallery3_query_type'] == '3.0.0'): ?>
					<p>
						Your Gallery3 install seems to be version 3.0.0. This is fine and will work with this 
						plugin, but please consider upgrading to Gallery3 3.0.1 since it contains fixes affecting 
						the performance of this plugin. 
					</p>
					<?php if ($optionarray_def['gallery3_maxlength'] == 512): ?>
						<p>Also, the server requires GET variables to be rather short. You might have <a href="http://www.hardened-php.net/suhosin/">Suhosin</a> 
						installed, in which case it can be reconfigured to allow for longer GET vars, which 
						in turn will allow for greater performance for this plugin.</p>
						<p>If you have access to the Gallery installation, try dropping 
						'suhosin.get.max_value_length = 10000;' into your php.ini. Then click Update Options (below) again.</p>
					<?php endif ?>
					<span>Maximum acceptable request var length: <?= $optionarray_def['gallery3_maxlength']; ?></span><br />
				<?php else: ?>
					<p>
						Detected REST tree support, will use this.
					</p>
				<?php endif ?>
				
                               <input type="submit" name="submit" value="<?php _e('Update Options') ?> &raquo;" />
			</div>
		</form>
		</div>
	
		<?
	}
}

?>
