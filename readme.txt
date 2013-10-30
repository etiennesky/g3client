=== G3Client ===
Author: Florian Stoffel
Contributors:
Donate link:
Tags: image, gallery, gallery3, g3
Requires at least: 3.0.0
Tested up to: 3.0.5
Stable tag: 0.2.0

G3Client embedds Gallery3 resources into a Wordpress posting or page.


== Description ==

G3Client embedds Gallery3 resources (albums, photos) into a Wordpress posting or page.
It requires the following software available on the server:

*   PHP 5 (tested with 5.3, recommended)
*   Wordpress 3 (tested with 3.0.5)
*   Gallery3 with REST API (tested with Gallery3 3.0.1)
*   PHP CURL Extension or HTTP_Request2 (http://pear.php.net/package/HTTP_Request2/)


== Installation ==

1. Upload g3client.zip to the `/wp-content/plugins/` directory
2. Unzip g3client-VERSION.zip
3. Install and activate `G3Client` in the plugin manager
4. Make sure you've enabled the rest api in Gallery3
5. Open `Settings` -> `G3Client`
6. Set the Gallery3 api settings (api base url and api key)
7. Save the settings. G3Client checks the connection to Gallery3, if you don't see an error
   message, G3Client should work
7. Add the shortcode to a post/page: `[g3client]`

You can easily modify the default style sheet, it is located in `/wp-content/plugins/g3client/css/`.

There is also the possibility to load a user defined style sheet (see the option page for details).


== Frequently Asked Questions ==

= Can http basic authentication be used with G3Client? =

Yes, just specify it in the Gallery3 api base url as follows:

http://username:password@example.com/gallery3/index.php/rest/

= How can I translate G3Client into my language? =

The `.pot` file is supplied in the directory `g3client/languages`. Together with Poedit, you can
translate all messages of G3Client.

== Screenshots ==

1. The preferences page
2. The embedded gallery created by G3Client (default style sheet)
3. Embedded gallery showing an album containing an album and photos
4. Full image view using the lightbox (fancybox)


== Upgrade Notice ==

No interaction required


== Changelog ==

= 0.2.0 =
* added siderbar widget to display a random image from Gallery3
* added custom album and photo listing titles
* fixed url generation errors
* fixed various small problems
* admin interface improvements
* refactorings and extensions to simplify the creation of new widgets and
  output modules

= 0.1.5 =
* added lightbox compatibility mode to prevent doubled lightboxes, especially when having
  jQuery Colorbox installed, thx to admaust
* added the possibility to include a user defined css file (see `g3client/css/user.css` for
  a template), the user css file will disable the default css, thx to admaust
* added some more shorttags and display customizations
* internal optimizations

= 0.1.4 =
* fixed broken urls, thx to admaust

= 0.1.3 =
* fixed versioning

= 0.1.2 =
* removed HTTP_Request2 dependency, the more common php curl extension is now used by default
* added missing shortcode documentation for items per row

= 0.1.1 =
* first public release
