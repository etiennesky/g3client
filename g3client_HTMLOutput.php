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

include_once(dirname(__FILE__) . '/g3client_Output.php');

/** html output */
class G3Client_HTMLOutput extends G3Client_Output {

    public function __construct() {
        parent::__construct();
    }

	public function generateView($toShow) {
		$toShow = $this->client->getItem($toShow,'',$this->getOption(G3_SETTINGS_SHOWCHILDREN));

		if(is_array($toShow) && isset($toShow['failure']))
			return $this->getErrorMessage($toShow);

		//$result = '<div class="g3client_wrapper">';
		$result = '<div class="g3client_wrapper ' . $this->getOption(G3_SETTINGS_ITEM_CLASS, '') . '" >';

		$result .= $this->generateBreadcrumb($toShow);

		if($toShow['curitem']['type'] == 'album') {
			if($this->getOption(G3_SETTINGS_SHOWALBUMHEADING, true) &&
			    !empty($toShow['albums']) && $toShow['curitem']['id'] != 1) {
				$result .= '<h3 class="g3client_albumscaption">';
				$result .= '<h3 class="'.$class.'">';
 			    $albumsHeading = $this->getOption(G3_SETTINGS_ALBUMSHEADING, '');
			    if(empty($albumsHeading)) $albumsHeading = __('There are other albums to see here...', 'g3client');

			    $albumsHeading = $this->replaceTags($albumsHeading, '%children%', count($toShow['albums']));

				$result .= $albumsHeading;
				$result .= '</h3>';
			}

			$result .= $this->generateThumbView($toShow['albums']);

			if($this->getOption(G3_SETTINGS_SHOWPHOTOHEADING) && !empty($toShow['photos'])) {
				$result .= '<h3 class="g3client_photoscaption">';
			    $photosHeading = $this->getOption(G3_SETTINGS_PHOTOSHEADING, '');

			    if(empty($photosHeading)) $photosHeading = __('Photos in this album (%views%)', 'g3client');

                $photosHeading = $this->replaceTags($photosHeading, $toShow['curitem']);

			    $result .= $photosHeading;

				$result .= '</h3>';
			}

			$result .= $this->generateThumbView($toShow['photos'], 'group-' . $toShow['curitem']['id']);
		} else {
			$result .= $this->generateSingleView($toShow['curitem']);
		}

		$result .= '</div>';

		return $result;
	}

	public function getName() {
		return 'HTML Output';
	}

	private function getErrorMessage($data) {
		$result = '<div class="error"><strong>';
		$result .= __('Could not retrieve Gallery3 data', 'g3client');
		$result .= ':</strong> ';
		$result .= $data['msg'];
		if(isset($data['http_status']))
			$result .= ' (http status code ' . $data['http_status'] . ')';
		$result .= '</div>';

		return $result;
	}

	private function generateThumbView($items, $rel = '') {
		if(count($items) == 0) 
		  return '';
		$result = '<table class="g3client_thumbview">';

		$i = 0;
		$isFirstRow = true;
		while($i < count($items)) {
			if($i % $this->getOption(G3_SETTINGS_ITEMS_PER_ROW) == 0) {
				if(!$isFirstRow) $result .= '</tr>';
				$result .= '<tr>';
				$isFirstRow = false;
			}

			$result .= $this->getThumbImg($items[$i], $rel);

			$result .= '</td>';

			$i++;
		}

		$result .= '</tr>';

		$result .= '</table>';

		return $result;
	}

	private function generateSingleView1($item) {
		$result = '<div class="g3client_singleview">';

		// TODO add g3client_image class for lightbox 
		// and add a g3client_wrapper_single class to fix span
		//$result .= '<img src="' . $item['imgurl']  . '" alt="">';
		if($this->getOption(G3_SETTINGS_SINGLESIZE) == 'thumb')
			$result .= '<img src="' . $item['thumb']  . '" alt="" title="' . $item['title'] . '">';
		else if($this->getOption(G3_SETTINGS_SINGLESIZE) == 'full')
			$result .= '<img src="' . $item['full_imgurl']  . '" alt="" title="' . $item['title'] . '">';
		else
			$result .= '<img src="' . $item['imgurl']  . '" alt="" title="' . $item['title'] . '">';

 		if($this->getOption(G3_SETTINGS_SHOWSINGLETITLES))
		  $result .= '<p class="g3client_singletitle">' . $item['title'] . '</p>';
		if($this->getOption(G3_SETTINGS_SHOWSLUGINSINGLEVIEW) && !empty($item['slug']) &&
		    strcmp($item['slug'], $item['title']) != 0)
		        $result .= '<p class="g3client_singleslug">' . $item['slug']  .'</p>';
		$result .= '</div>';

		return $result;
	}

	// TODO add a g3client_wrapper_single class to fix span
	private function generateSingleView($curItem) {
		$result = '<div class="g3client_singleview">';

		// previous code was just <img> without <a>
		//$result .= '<img src="' . $item['imgurl']  . '" alt="">';

		$slug = !empty($curItem['slug']) ? $curItem['slug'] : $curItem['title'];

		// <a>
		if($this->getOption(G3_SETTINGS_USELIGHTBOX) && isset($curItem['imgurl'])) {
			$result .= '<a href="' . $curItem['imgurl'] . '" title="' . $curItem['title'] . '"';
			$result .= ' class="' . $this->getHrefCSS(array('g3client_image')) . '"';
			$rel = 'post';
			if(!empty($rel)) $result .= ' rel="' . $rel . '"';
            if( $curItem['imgurl'] != $curItem['full_imgurl'] && 
                $curItem['img_height'] != $curItem['full_img_height'] &&
                $curItem['img_width'] != $curItem['full_img_width'] )
                $result .= ' data-fullimg-href="' .$curItem['full_imgurl']. '"';
			$result .= '>';
		} else {// TODO fix URL when not using lightbox script
			$result .= '<a href="' . G3Client_Output::genURL(array('item' => $curItem['id']))  . '" title="' . $slug . '">';
		}

		// <img>
		if($this->getOption(G3_SETTINGS_SINGLESIZE) == 'thumb')
			$img = $curItem['thumb'];
		else if($this->getOption(G3_SETTINGS_SINGLESIZE) == 'full')
			$img = $curItem['full_imgurl'];
		else
			$img = $curItem['imgurl'];
		$result .= '<img src="' . $img . '" alt="' . $slug . '">';

		$result .= '</a>';

		// title + slug
 		if($this->getOption(G3_SETTINGS_SHOWSINGLETITLES))
		  $result .= '<p class="g3client_singletitle">' . $curItem['title'] . '</p>';
		if($this->getOption(G3_SETTINGS_SHOWSLUGINSINGLEVIEW) && !empty($curItem['slug']) &&
		    strcmp($curItem['slug'], $curItem['title']) != 0)
		        $result .= '<p class="g3client_singleslug">' . $curItem['slug']  .'</p>';

		$result .= '</div>';

		return $result;
	}

	private function getThumbImg($curItem, $rel = '') {
		$slug = !empty($curItem['slug']) ? $curItem['slug'] : $curItem['title'];

		$result = '<td class="g3client_thumb g3client_thumb_' . $curItem['type'] . '">';

		if($this->getOption(G3_SETTINGS_USELIGHTBOX) && isset($curItem['imgurl'])) {
			$result .= '<a href="' . $curItem['imgurl'] . '" title="' . $curItem['title'] . '"';
			$result .= ' class="' . $this->getHrefCSS(array('g3client_image')) . '"';
			if(!empty($rel)) $result .= ' rel="' . $rel . '"';
            if( $curItem['imgurl'] != $curItem['full_imgurl'] && 
                $curItem['img_height'] != $curItem['full_img_height'] &&
                $curItem['img_width'] != $curItem['full_img_width'] )
                $result .= ' data-fullimg-href="' .$curItem['full_imgurl']. '"';
			$result .= '>';
		} else {
			$result .= '<a href="' . G3Client_Output::genURL(array('item' => $curItem['id']))  . '" title="' . $slug . '">';
		}

		$result .= '<img src="' . $curItem['thumb'] . '" alt="' . $slug . '">';
		$result .= '</a>';

		if($curItem['type'] == 'album' || $this->getOption(G3_SETTINGS_SHOWTHUMBTITLES)) {
			$result .= '<p class="g3client_thumb_title">';
			$result .= $curItem['title'];
			$result .= '</p>';
		}

		return $result;
	}
}
?>
