=== Plugin Name ===
Contributors: andsten
Tags: gallery3, photos, photo, media, image, images, gallery
Requires at least: 2.8
Tested up to: 3.0.3
Stable tag: 0.9.5

Adds a media picker pane allowing access to media from a Gallery3 installation.

== Description ==

This plugin will allow WordPress to link or import media from a specified Gallery 3 install, much in the way the WP Media library does. It does not provide any front-end sugar, such as carousels or daily images with an automatic rotation.

If you run a Gallery with many albums (hundreds), it might take a decent while to fetch the album list. This is a limitation in Gallery 3.0.0 - upgrading to (the as of yet unreleased) Gallery 3.0.1 will solve this.

Bits missing before this is release 1.0 worthy:
* Translation support.
* More progress indicators.
* More picker polish.

== Installation ==

1. Upload the `gallery3-picker` directory to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Make sure to update the settings in Settings/Gallery3 media picker.

== Frequently Asked Questions ==

= So what's the difference between this plugin and other Gallery3 plugins? =

heiv gallery 3:
* Image picker
* No WP media library integration.
* Allows for embeddable carousels. 
* Requires visual editor mode in order to invoke it.
* Fairly slow navigation - will query new parts of the folder structure every time you enter a new album and images are paged. 
* Plug specific markup.

GWPG3:
* Alpha stage of development, more ambitious plan than the other two plugins.
* For now, no image picker.
* Plug specific markup.

Gallery3 Picker: 
* Image picker.
* Media library integration.
* Fast navigation, progressive thumbnail loading.
* Anything you can do with images in the media library is (naturally) possible, including the default resizing and positioning controls. 
* Images are served out of WordPress or linked directly from Gallery3.
* Proper support for private albums.
* No plug specific markup (if you grow tired of the plugin, remove it and images in old posts/pages will still work).
* rest/tree support, allowing very quick navigation (Gallery 3.0.1 and up)

== Screenshots ==

1. The picker view in the `Add Media` dialog.
2. Image insertion dialog.

== Changelog ==

= 0.9.5 =
* Support for the upcoming Gallery3 3.0.1 rest/tree interface, allowing much quicker album tree and photo list loading.
* Cleanups in the settings interface, mainly under the hood.

= 0.9.4 =
* Chop large requests up into several smaller requests, in order to cope with Suhosin defaults and other GET length limiting proxies and servers. We autoprobe for the longest permissible length when we save the config. Thanks to shecter for providing a rather large test subject.
* Show the image resize file size if we're not allowed to access the full size image.
* Allow insertion of either of all three image sizes if we got permission to do so.
* Fixed an issue where you couldn't deep link images if you had the visual mode post editor open.

= 0.9.3.1 =
* Submitting file that managed to slip away in the last commit. Whoops.

= 0.9.3 =
* Whole new interface for dealing with direct linking of Gallery3 images.
* Much improved support for non-public albums.
* Messier code, merging parts of this plugin with parts of a sibling plugin for MediaWiki. Will cleanup before 1.0.

= 0.9.1 =
* Support for both mod_rewrite and non_mod_rewrite Gallery3 installations.
* The configuration is tested (against the Gallery) upon save.
* Leaner, meaner jstree version - only the plugins we need + it's minified.
* Fixed an annoying off-by-one bug in gallery.js, introduced in 0.9.
* Fixed a bug where the API key wouldn't be sent on some requests.

= 0.9 =
* Progressive fetching of images in the preview pane - the browser will fetch thumbnails on scroll rather than all at once.
* IE fixes, the plugin now works with any IE from 6.0 and onwards.

= 0.8.1 =
* Now lifting the description field from Gallery3 into the description field of the Wordpress attachments.

= 0.8 =
* Initial release

== Upgrade Notice ==

When you upgrade, be sure to save settings in the configuration panel for the plugin, even if you don't do any actual changes. This is also true when you upgrade your Gallery install to 3.0.1.