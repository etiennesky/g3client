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

/*
Plugin Name: G3Client
Description: A simple client for the excellent Gallery3
Version: 0.2.0
Author: Florian Stoffel <fstoffel@users.sourceforge.net>
License: MIT
*/

include_once(dirname(__FILE__) . '/g3client_Admin.php');
include_once(dirname(__FILE__) . '/g3client_Shortcode.php');
include_once(dirname(__FILE__) . '/widgets/g3client_RandomPhotoWidget.php');

define('G3_SETTINGS_APIURL', 'g3_restapiurl');
define('G3_SETTINGS_APIKEY', 'g3_restapikey');
define('G3_SETTINGS_OUTPUTFORMATTER', 'g3_outputformatter');
define('G3_SETTINGS_ITEMS_PER_ROW', 'g3_itemsperrow');
define('G3_SETTINGS_SHOWSLUGINSINGLEVIEW', 'g3_showsluginsingleview');
define('G3_SETTINGS_SHOWBREADCRUMB', 'g3_showbreadcrumb');
define('G3_SETTINGS_SHOWTHUMBTITLES', 'g3_showthumbtitles');
define('G3_SETTINGS_USELIGHTBOX', 'g3_uselightbox');
define('G3_SETTINGS_SHOWALBUMHEADING', 'g3_showalbumheading');
define('G3_SETTINGS_SHOWPHOTOHEADING', 'g3_showphotoheading');
define('G3_SETTINGS_LIGHTBOXCOMPATMODE', 'g3_lightboxcompatmode');
define('G3_SETTINGS_USERCSSFILE', 'g3_usercssfile');
define('G3_SETTINGS_ALBUMSHEADING', 'g3_albumsheading');
define('G3_SETTINGS_PHOTOSHEADING', 'g3_photosheading');

add_action('init', 'G3Client_Init');
add_action('widgets_init', 'G3Client_RegisterWidgets');

if(is_admin()){
    add_action('admin_menu', 'G3Client_AdminMenuHook');
    add_action('admin_init', 'G3Client_RegisterSettings');

    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'G3Client_PluginActionLinks');
    add_filter('plugin_row_meta', 'G3Client_PluginMetaLinks', 10, 2);

    if(!get_option(G3_SETTINGS_APIKEY) || !get_option(G3_SETTINGS_APIURL)) {
        add_action('admin_notices', 'G3Client_AdminSettingsWarning');
    }
}

/** settings incomplete warning */
function G3Client_AdminSettingsWarning(){
    $warning = '<div id="akismet-warning" class="updated fade"><p><strong>';
    $warning .= __('G3Client is almost ready.', 'g3client') . '</strong> ';
    $warning .= sprintf(__('You must <a href="%1$s">specify the Gallery3 API Settings</a> for it to work.'), 'options-general.php?page=' . plugin_basename(__FILE__));
    $warning .= '</p></div>';

    echo $warning;
}

/** admin menu hooks */
function G3Client_AdminMenuHook() {
	add_submenu_page('options-general.php', __('G3Client Settings', 'g3client'),
		__('G3Client', 'g3client'), 'manage_options', plugin_basename(__FILE__),
		'G3Client_AdminPage');
}

/** adds plugin action links */
function G3Client_PluginActionLinks($links) {
    $settingsLink = '<a href="options-general.php?page=' . plugin_basename(__FILE__) . '">' . __('Settings') . '</a>';

    array_unshift($links, $settingsLink);

    return $links;
}

/** add plugin meta links */
function G3Client_PluginMetaLinks($links, $file){
    if($file == plugin_basename(__FILE__)) {

    }

    return $links;
}

/** registers the settings */
function G3Client_RegisterSettings() {
	register_setting('g3client-settings', G3_SETTINGS_APIURL);
	register_setting('g3client-settings', G3_SETTINGS_APIKEY);
	register_setting('g3client-settings', G3_SETTINGS_OUTPUTFORMATTER);
	register_setting('g3client-settings', G3_SETTINGS_ITEMS_PER_ROW);
	register_setting('g3client-settings', G3_SETTINGS_SHOWSLUGINSINGLEVIEW);
	register_setting('g3client-settings', G3_SETTINGS_SHOWBREADCRUMB);
	register_setting('g3client-settings', G3_SETTINGS_SHOWTHUMBTITLES);
	register_setting('g3client-settings', G3_SETTINGS_USELIGHTBOX);
	register_setting('g3client-settings', G3_SETTINGS_LIGHTBOXCOMPATMODE);
	register_setting('g3client-settings', G3_SETTINGS_SHOWALBUMHEADING);
	register_setting('g3client-settings', G3_SETTINGS_SHOWPHOTOHEADING);
	register_setting('g3client-settings', G3_SETTINGS_USERCSSFILE);
    register_setting('g3client-settings', G3_SETTINGS_ALBUMSHEADING);
    register_setting('g3client-settings', G3_SETTINGS_PHOTOSHEADING);
}

/** initializes g3client */
function G3Client_Init() {
    // i18n
	load_plugin_textdomain('g3client', false, dirname(plugin_basename(__FILE__)) . '/languages/');

    // do not load js/css files when the backend is active
	if(is_admin()) return;

    // load the lightbox script to make sure the widget works properly
    wp_enqueue_script('jquery-fancybox', plugins_url('fancybox/jquery.fancybox-1.3.4.pack.js', __FILE__), array('jquery'), '1.3.4', true);
    wp_enqueue_script('jquery-ease', plugins_url('fancybox/jquery.easing-1.3.pack.js', __FILE__), array('jquery'));
    wp_enqueue_script('jquery-mousehweel', plugins_url('fancybox/jquery.mousewheel-3.0.4.pack.js', __FILE__), array('jquery'));
    wp_enqueue_script('g3client-initfancybox', plugins_url('fancybox/init.fancybox.js', __FILE__), array('jquery', 'jquery-fancybox'), '1.0', true);
    wp_enqueue_style('fancybox', plugins_url('fancybox/jquery.fancybox-1.3.4.css', __FILE__));

    // user css
	$userCSS = get_option(G3_SETTINGS_USERCSSFILE, '');

    if(!empty($userCSS)) {
		wp_enqueue_style('g3client-user', get_bloginfo('stylesheet_directory') . '/' . $userCSS);
	} else {
		wp_enqueue_style('g3client', plugins_url('css/g3client.css', __FILE__));
	}
}

function G3Client_RegisterWidgets(){
    // register random photo widget
    register_widget('G3Client_RandomPhotoWidget');
}

?>
