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

abstract class G3Client_Output {

    /** shortcode options */
    private $shortcodeOptions = array();
    /** rest api client */
    protected $client;

    protected function __construct() {
        $this->client = new G3Client($this->getOption(G3_SETTINGS_APIURL),
            $this->getOption(G3_SETTINGS_APIKEY));
    }

    /**
     * Sets the output options
     *
     * @param options the options to set
     */
    public function setOutputOptions($options = array()) {
        $this->shortcodeOptions = $options;
    }

    /**
     * Generates a view for the given item
     *
     * @param toShow the item to generate the view for
     * @return the view for the given item
     */
    public abstract function generateView($toShow);

    /**
     * Returns the name of the output module
     *
     * @return the name of the output module
     */
    public abstract function getName();

    /**
     * Generates a breadcrumb for the given item
     *
     * @param curItem the item to generate the breadcrumb for
     */
    protected function generateBreadcrumb($curItem) {
        //if(empty($curItem) || !$this->getOption(G3_SETTINGS_SHOWBREADCRUMB)) return '';
        if(empty($curItem)) return '';
		if($curItem['curitem']['type'] == 'album')
		{
			if(!$this->getOption(G3_SETTINGS_SHOWBREADCRUMB_ALBUM)) return '';
		}
	    else {
			if(!$this->getOption(G3_SETTINGS_SHOWBREADCRUMB_PHOTO)) return '';
		}

        $result = '<p class="g3client_breadcrumb">';
        $result .= __('You are here', 'g3client');
        $result .= ': ';

        foreach(array_reverse($curItem['parents']) as $curParent) {
            $result .= '<span>';
            $result .= '<a href="';
            $result .= G3Client_Output::genURL(array('item' => $curParent['id']));
            $result .= '" title="';
            $result .= !empty($curParent['slug']) ? $curParent['slug'] : $curParent['title'];
            $result .= '">';
            $result .= $curParent['title'];
            $result .= '</a>';
            $result .= '</span> &middot; ';
        }

        $result .= '<span class="g3client_curitem">';
        $result .= $curItem['curitem']['title'];
        $result .= '</span>';

        $result .= '</p>';

        return $result;
    }

    /**
     * Generates the class names for a link to an image. Realizes the lightbox
     * compatibility mode
     *
     * @param classNames an array of additional class names
     * @return the class names for an image link
     */
    public static function getHrefCSS($classNames = array()) {
        if(get_option(G3_SETTINGS_LIGHTBOXCOMPATMODE, 'off') == 'on') {
            // jquery colorbox
            $classNames[]= 'colorbox-off';
            // more to come?
        }

        return implode(' ', $classNames);
    }

    /**
     * Returns the value of a given (short)code option
     *
     * @param option the option to retrieve
     * @param default the default value of the option to retrieve
     * @return the value of the option to retrieve, it the option is not found
     * or not set, {@code default} will be returned
     */
    protected function getOption($option, $default = '') {
        if(array_key_exists($option, $this->shortcodeOptions))
            return $this->shortcodeOptions[$option];

        return get_option($option, $default);
    }

    /**
     * Replaces the text variables with the item data
     *
     * @param toReplace the text to use for replacements
     * @param item the item to get the data of
     * @return the text with the replaced strings
     */
    protected function replaceTags($toReplace, $item = array(), $replacement = false) {
        if(empty($item)) return $toReplace;

        if(!$replacement) {
            $result = $toReplace;
            if($item['type'] == 'album') {
                $result = str_replace(
                    array('%title%', '%slug%', '%children%', '%views%'),
                    array($item['title'], $item['slug'], $item['children'] - 1, $item['viewcount']),
                    $toReplace);
            } else if($item['type'] == 'photo') {
                $result = str_replace(
                    array('%title%', '%slug%', '%views%'),
                    array($item['title'], $item['slug'], $item['viewcount']),
                    $toReplace);
            }
        } else {
            $result = str_replace($item, $replacement, $toReplace);
        }


        return $result;
    }

    /**
     * Generates a url to the current page with a given set of GET parameters
     *
     * @param params the parameters to be appended to the url
     * @return the url of to the current page with the given GET paramters
     */
	public static function genURL($params = array()) {
		global $_SERVER;

		$baseURL = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
		$baseURL .= $_SERVER['SERVER_NAME'] .
			($_SERVER['SERVER_PORT'] != '80' ? $_SERVER['SERVER_PORT'] : '') .
			$_SERVER['REQUEST_URI'];

	    $urlData = parse_url($baseURL);
	    $args = array();

	    foreach(explode('&', $urlData['query']) as $curArg) {
	        $argData = explode('=', $curArg);
	        $args[$argData[0]] = isset($argData[1]) ? $argData[1] : '';
	    }

		if(!empty($params)) {
		    $args = array_merge($args, $params);

		    $newArgs = '';
		    foreach($args as $key => $value) {
		        $newArgs.= $key . '=' . urlencode($value) . '&';
		    }

		    $urlData['query'] = rtrim($newArgs, '&');
		}

	    return G3Client_Output::glueURL($urlData);

	}

    // thx to ilja at radusch dot com, seen @ http://php.net/manual/de/function.parse-url.php
    private static function glueURL($parsed) {
        if (!is_array($parsed)) return false;

        $uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '' : '//') : '';
        $uri .= isset($parsed['user']) ? $parsed['user'].(isset($parsed['pass']) ? ':'.$parsed['pass'] : '').'@' : '';
        $uri .= isset($parsed['host']) ? $parsed['host'] : '';
        $uri .= isset($parsed['port']) ? ':'.$parsed['port'] : '';

        if (isset($parsed['path'])) {
            $uri .= (substr($parsed['path'], 0, 1) == '/') ?
            $parsed['path'] : ((!empty($uri) ? '/' : '' ) . $parsed['path']);
        }

        $uri .= isset($parsed['query']) ? '?' . $parsed['query'] : '';
        $uri .= isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';

        return $uri;
    }

    /**
     * Parses a given value into a boolean value
     *
     * @param val the value to parse
     * @return the boolean value of the given value
     */
    public static function parseBoolean($val) {
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

}

?>