<?php
/*
Copyright (C) 2011 by Florian Stoffel

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
include_once(dirname(__FILE__) . '/client.php');

function G3Client_AdminPage() {
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"><br /></div>
<h2><?php _e('G3Client Settings', 'g3client') ?></h2>
<?php G3Client_Validate(); ?>
<form method="post" action="options.php">
<?php settings_fields('g3client-settings') ?>
<h3><?php _e('Gallery3 API Settings', 'g3client') ?></h3>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e('Rest API URL', 'g3client') ?></th>
		<td>
			<input class="regular-text code" type="text" name="<?php echo G3_SETTINGS_APIURL ?>" value="<?php echo get_option(G3_SETTINGS_APIURL) ?>"/><br/>
			<span class="description"><?php _e('The base url of the rest api, for example: <code>http://www.example.com/gallery3/index.php/rest/</code>', 'g3client') ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('API Key', 'g3client') ?></th>
		<td>
			<input class="regular-text code" type="text" name="<?php echo G3_SETTINGS_APIKEY ?>" value="<?php echo get_option(G3_SETTINGS_APIKEY) ?>"/><br/>
			<span class="description"><?php _e('The rest api key, is available in the user\'s profile.', 'g3client') ?></span>
		</td>
	</tr>
</table>

<h3>Gallery Default Output Settings</h3>
<p class="description"><?php _e('These are the default settings. They can be overwritten by the shortcode attributes (see below)', 'g3client') ?></p>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e('Items per row', 'g3client') ?></th>
		<td>
			<input class="small-text code" type="text" name="<?php echo G3_SETTINGS_ITEMS_PER_ROW ?>" value="<?php echo get_option(G3_SETTINGS_ITEMS_PER_ROW, 4) ?>"/>
			<span class="description"><?php _e('Specifies the maximal number of photos or albums displayed in a row.', 'g3client') ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Show slug for single photos', 'g3client') ?></th>
		<td>
			<label><input type="checkbox" name="<?php echo G3_SETTINGS_SHOWSLUGINSINGLEVIEW ?>" <?php checked('on', get_option(G3_SETTINGS_SHOWSLUGINSINGLEVIEW))?>/> <?php _e('Display the photo slug in single photo view (does not apply if light box is enabled).', 'g3client'); ?></label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Show breadcrumb', 'g3client') ?></th>
		<td>
			<label><input type="checkbox" name="<?php echo G3_SETTINGS_SHOWBREADCRUMB ?>" <?php checked('on', get_option(G3_SETTINGS_SHOWBREADCRUMB)) ?>/> <?php _e('Show breadcrumb navigation on top of photo(s) or album(s).', 'g3client') ?></label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Thumbnail titles', 'g3client') ?></th>
		<td>
			<label><input type="checkbox" name="<?php echo G3_SETTINGS_SHOWTHUMBTITLES ?>" <?php checked('on', get_option(G3_SETTINGS_SHOWTHUMBTITLES)) ?>/> <?php _e('Show titles for thumbnails.', 'g3client') ?></label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Show albums heading', 'g3client') ?></th>
		<td>
			<label><input type="checkbox" name="<?php echo G3_SETTINGS_SHOWALBUMHEADING ?>" <?php checked('on', get_option(G3_SETTINGS_SHOWALBUMHEADING, 'on')) ?>/> <?php _e('Show an head line above the album listings.', 'g3client') ?></label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Show photos heading', 'g3client') ?></th>
		<td>
			<label><input type="checkbox" name="<?php echo G3_SETTINGS_SHOWPHOTOHEADING ?>" <?php checked('on', get_option(G3_SETTINGS_SHOWPHOTOHEADING, 'on')) ?>/> <?php _e('Show an head line above the album listings.', 'g3client') ?></label>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Use lightbox', 'g3client') ?></th>
		<td>
			<label><input type="checkbox" name="<?php echo G3_SETTINGS_USELIGHTBOX ?>" <?php checked('on', get_option(G3_SETTINGS_USELIGHTBOX)) ?>/> <?php _e('Use a lightbox script to display full images.', 'g3client') ?></label><br/>
		<span class="description">If you have problems with other lightbox plugins/scripts try to enable the <code>Lightbox Compatibility Mode</code> in the Miscellaneous section below.</span>
		</td>
	</tr>
</table>

<h3><?php _e('Output', 'g3client') ?></h3>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e('Albums listing title', 'g3client') ?></th>
		<td>
			<label><input class="regular-text code" type="text" name="<?php echo G3_SETTINGS_ALBUMSHEADING ?>" value="<?php echo get_option(G3_SETTINGS_ALBUMSHEADING, '') ?>"/> <?php _e('Define a custom header for the albums listings.', 'g3client') ?></label><br/>
			<span class="description"><?php _e('See the output variable table for the albums listing below.') ?></span>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('Photos listing title', 'g3client') ?></th>
		<td>
			<label><input class="regular-text code" type="text" name="<?php echo G3_SETTINGS_PHOTOSHEADING ?>" value="<?php echo get_option(G3_SETTINGS_PHOTOSHEADING, '') ?>"/> <?php _e('Define a custom header for the photos listings.', 'g3client') ?></label><br/>
            <span class="description"><?php _e('See the output variable table for the photos listing below.') ?></span>
		</td>
	</tr>
</table>

<h4><?php _e('Output Variables', 'g3client') ?></h4>
<table class="widefat" style="width: 50%;">
    <thead>
	<tr>
		<th style="width: 10em"><?php _e('Variable', 'g3client') ?></th>
		<th style="width: 25em"><?php _e('Replacement', 'g3client') ?></th>
		<th style="width: 20em"><?php _e('Applies to', 'g3client') ?></th>
	</tr>
	</thead>
	<tbody>
	    <tr>
	        <td><code>%children%</code></td>
	        <td><?php _e('the number of child albums', 'g3client') ?></td>
	        <td><?php _e('albums listing heading', 'g3client') ?>, <?php _e('photos listing heading', 'g3client') ?></td>
	    </tr>
	    <tr>
	        <td><code>%title%</code></td>
	        <td><?php _e('the title of the current album', 'g3client') ?></td>
	        <td><?php _e('photos listing heading', 'g3client') ?></td>
	    </tr>
	    <tr>
	        <td><code>%slug%</code></td>
	        <td><?php _e('the slug of the current album', 'g3client') ?></td>
	        <td><?php _e('photos listing heading', 'g3client') ?></td>
	    </tr>
	    <tr>
	        <td><code>%views%</code></td>
	        <td><?php _e('the number of views of the current album', 'g3client') ?></td>
	        <td><?php _e('photos listing heading', 'g3client') ?></td>
	    </tr>
	</tbody>
</table>

<h3><?php _e('Miscellaneous', 'g3client') ?></h3>
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e('Lightbox compatibility mode', 'g3client') ?></th>
		<td>
			<label><input type="checkbox" name="<?php echo G3_SETTINGS_LIGHTBOXCOMPATMODE ?>" <?php checked('on', get_option(G3_SETTINGS_LIGHTBOXCOMPATMODE, 'off')) ?>/> <?php _e('Enabling the compatibility mode can prevent problems when using other lightbox plugins/scripts together with G3Client\'s integrated one.', 'g3client') ?></label>		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e('User css file', 'g3client') ?></th>
		<td>
			<input class="regular-text code" type="text" name="<?php echo G3_SETTINGS_USERCSSFILE ?>" value="<?php echo get_option(G3_SETTINGS_USERCSSFILE, '') ?>"/><br/>
			<span class="description"><?php _e('The path to a user defined css file to include.') ?><br/><?php printf(__('The path has to be given relative to the current theme\'s style sheet directory <code>(%1$s)</code>.', 'g3client'), get_bloginfo('stylesheet_directory')) ?></span>
		</td>
	</tr>
</table>

<h3><?php _e('How to use G3Client', 'g3client'); ?></h3>
<ol>
	<li><?php _e('Open the page/post where you want the gallery to show up', 'g3client') ?></li>
	<li><?php _e('Add the following shortcode to the page/post contents:', 'g3client') ?><br/><code>[g3client]</code></li>
</ol>
<?php _e('Example shortcode')?>: <code>[g3client item=1 itemsperrow=4]</code>

<h4><?php _e('Shortcode options', 'g3client'); ?></h4>
<table class="widefat" style="width: 50%;">
    <thead>
	    <tr>
		    <th style="width: 10em"><?php _e('Option', 'g3client') ?></th>
		    <th style="width: 25em"><?php _e('Description', 'g3client') ?></th>
    		<th style="width: 20em"><?php _e('Value(s)', 'g3client') ?></th>
	    </tr>
	</thead>
	<tbody>
    	<tr>
    		<td><code>item</code></td>
    		<td><?php _e('specifies the item to show', 'g3client') ?></td>
    		<td><?php _e('a numeric Gallery3 id', 'g3client') ?></td>
    	</tr>
    	<tr>
    		<td><code>itemsperrow</code></td>
    		<td><?php _e('specifies the max number of items per row', 'g3client') ?></td>
    		<td><?php _e('a number', 'g3client') ?></td>
    	</tr>
    	<tr>
    		<td><code>showslug</code></td>
    		<td><?php _e('to show the slug in a single photo view', 'g3client') ?></td>
    		<td><code>yes</code> <?php _e('or', 'g3client') ?> <code>no</code></td>
    	</tr>
    	<tr>
    		<td><code>breadcrumb</code></td>
    		<td><?php _e('show the breadcrumb navigation', 'g3client') ?></td>
    		<td><code>yes</code> <?php _e('or', 'g3client') ?> <code>no</code></td>
    	</tr>
    	<tr>
    		<td><code>thumbtitles</code></td>
    		<td><?php _e('show the thumbnail titles', 'g3client') ?></td>
    		<td><code>yes</code> <?php _e('or', 'g3client') ?> <code>no</code></td>
    	</tr>
    	<tr>
    		<td><code>albumheading</code></td>
    		<td><?php _e('show a head line above the albums listing', 'g3client') ?></td>
    		<td><code>yes</code> <?php _e('or', 'g3client') ?> <code>no</code></td>
    	</tr>
    	<tr>
    		<td><code>photoheading</code></td>
    		<td><?php _e('show a head line above the photos listing', 'g3client') ?></td>
    		<td><code>yes</code> <?php _e('or', 'g3client') ?> <code>no</code></td>
    	</tr>
    	<tr>
    		<td><code>lightbox</code></td>
    		<td><?php _e('show photos with a lightbox', 'g3client') ?></td>
    		<td><code>yes</code> <?php _e('or', 'g3client') ?> <code>no</code></td>
    	</tr>
    </tbody>
</table>

<p class="submit">
<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
</p>
</form>
</div>
<?php
}

function G3Client_Validate() {
	$apiKey = get_option('g3_restapikey');
	$apiURL = get_option('g3_restapiurl');

	if(!$apiKey || !$apiURL) return;

	$client = new G3Client($apiURL, $apiKey);
	$data = $client->getItem();

	if(is_array($data) && isset($data['failure'])) {
		echo '<div class="error"><p><strong>';
		_e('Could not connect to Gallery3', 'g3client');
		echo ': </strong>';
		echo isset($data['msg']) ? $data['msg'] : __('failed to connect to Gallery3');
		if(isset($data['http_status'])) {
			echo ' (';
			_e('http status code', 'g3client');
			echo ': ' . $data['http_status'] . ')';
		}
		echo '</p></div>';
	}
}
?>
