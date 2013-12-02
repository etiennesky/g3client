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

include_once(dirname(__FILE__) . '/g3client_HTMLOutput.php');

add_shortcode('g3client', 'G3Client_Shortcode_Handler');

function G3Client_Shortcode_Handler($atts) {
	global $_GET;

	$itemsPerRowDefault = get_option(G3_SETTINGS_ITEMS_PER_ROW, 3);
	if(!is_numeric($itemsPerRowDefault)) $itemsPerRowDefault = 3;

	$slugInSingleViewDefault = get_option(G3_SETTINGS_SHOWSLUGINSINGLEVIEW, 'off');
	$breadcrumbAlbumDefault = get_option(G3_SETTINGS_SHOWBREADCRUMB_ALBUM, 'on');
	$breadcrumbPhotoDefault = get_option(G3_SETTINGS_SHOWBREADCRUMB_PHOTO, 'on');
	$thumbtitlesDefault = get_option(G3_SETTINGS_SHOWTHUMBTITLES, 'off');
	$singletitlesDefault = get_option(G3_SETTINGS_SHOWSINGLETITLES, 'off');
	$lightboxDefault = get_option(G3_SETTINGS_USELIGHTBOX, 'on');
	$albumHeading = get_option(G3_SETTINGS_SHOWALBUMHEADING, 'on');
	$photoHeading = get_option(G3_SETTINGS_SHOWPHOTOHEADING, 'on');
	$showChildren = get_option(G3_SETTINGS_SHOWCHILDREN, 'on');
	$singleSizeDefault = get_option(G3_SETTINGS_SINGLESIZE, 'resize');
	$class = ''; // TODO add default class value?

	if( array_key_exists( 'breadcrumb', $atts ) ) {
		$atts[ 'breadcrumbAlbum' ] = $atts[ 'breadcrumb' ];
		$atts[ 'breadcrumbPhoto' ] = $atts[ 'breadcrumb' ];
	}

	extract(shortcode_atts(array(
		'item' => -1,
		'output' => 'html',
		'itemsperrow' => $itemsPerRowDefault,
		'sluginsingleview' => $slugInSingleViewDefault,
		'breadcrumbAlbum' => $breadcrumbAlbumDefault,
		'breadcrumbPhoto' => $breadcrumbPhotoDefault,
		'thumbtitles' => $thumbtitlesDefault,
		'singletitles' => $singletitlesDefault,
		'lightbox' => $lightboxDefault,
		'albumheading' => $albumHeading,
		'photoheading' => $photoHeading,
		'children' => $showChildren,
		'singlesize' => $singleSizeDefault,
		'class' => $class
	), $atts));

	if(isset($_GET['item']) && is_numeric($_GET['item']))
		$item = $_GET['item'];

	$outputFormatter = false;

	$sluginsingleview = G3Client_ParseBoolean($sluginsingleview);
	$breadcrumbAlbum = G3Client_ParseBoolean($breadcrumbAlbum);
	$breadcrumbPhoto = G3Client_ParseBoolean($breadcrumbPhoto);
	$thumbtitles = G3Client_ParseBoolean($thumbtitles);
	$singletitles = G3Client_ParseBoolean($singletitles);
	$lightbox = G3Client_ParseBoolean($lightbox);
	$albumHeading = G3Client_ParseBoolean($albumHeading);
	$photoHeading = G3Client_ParseBoolean($photoHeading);
    $showChildren = G3Client_ParseBoolean($children);

	switch(strtolower($output)) {
		case 'html':
			$outputFormatter = new G3Client_HTMLOutput(
				$itemsperrow, $sluginsingleview, $breadcrumbAlbum, 
				$breadcrumbPhoto, $thumbtitles, $singletitles, $albumHeading,
				$photoHeading, $lightbox, $showChildren, $singlesize, $class);
			break;

		default:
			$outputFormatter = new G3Client_HTMLOutput(
				$itemsperrow, $sluginsingleview, $breadcrumbAlbum, 
				$breadcrumbPhoto, $thumbtitles, $singletitles, $albumHeading,
				$photoHeading, $lightbox, $showChildren, $singlesize, $class);

	}

    if($outputFormatter == false) {
        return __('Internal error: could not select G3Client output formatter', 'g3client');
    } else {

        $outputFormatter->setOutputOptions(array(
            G3_SETTINGS_ITEMS_PER_ROW => $itemsperrow,
            G3_SETTINGS_SHOWBREADCRUMB_ALBUM => $breadcrumbAlbum,
            G3_SETTINGS_SHOWBREADCRUMB_PHOTO => $breadcrumbPhoto,
            G3_SETTINGS_SHOWTHUMBTITLES => $thumbtitles,
            G3_SETTINGS_SHOWSINGLETITLES => $singletitles,
            G3_SETTINGS_USELIGHTBOX => $lightbox,
            G3_SETTINGS_SHOWALBUMHEADING => $albumHeading,
            G3_SETTINGS_SHOWPHOTOHEADING => $photoHeading,
            G3_SETTINGS_SHOWCHILDREN => $showChildren,
            G3_SETTINGS_SINGLESIZE => $singlesize,
            G3_SETTINGS_ITEM_CLASS => $class
        ));

        return $outputFormatter->generateView($item);
    }
}

function G3Client_ParseBoolean($val) {
	return filter_var($val, FILTER_VALIDATE_BOOLEAN);
}

?>
