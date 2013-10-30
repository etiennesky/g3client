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

/** output utilities */
class G3Client_OutputUtil {

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

	    return G3Client_OutputUtil::glueURL($urlData);

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
