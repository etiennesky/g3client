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

/**
 * A simple client for the Gallery3 REST API
 */
class G3Client {
    /** the http user agent */
	private static $USER_AGENT = 'G3Client 1.1';
    /** rest api base url */
	private $baseURL;
    /** api key */
	private $apiKey;
    /** request cache */
	private $requestCache = array();
    /** whether to use the pear http request component */
	private $useHTTPReq2 = false;

    /**
     * Creates a new instance of {@code G3Client}
     *
     * @param baseURL the api base url
     * @param apiKey the api key
     */
	public function __construct($baseURL, $apiKey) {
		$this->setURL($baseURL);
		$this->apiKey = $apiKey;

		$this->useHTTPReq2 = (class_exists('HTTP_Request2') && !method_exists('curl_init'));

		if($this->useHTTPReq2) require_once('HTTP/Request2.php');
	}


	private function setURL($url) {
		$this->baseURL = $url;
	}

	private function request($resource, $isURL = false) {
		if(!$isURL) $resource = $this->baseURL . $resource;

		if(isset($this->requestCache[$resource])) return $this->requestCache[$resource];

		return ($this->useHTTPReq2) ? $this->requestHTTP2($resource) : $this->requestCURL($resource);
	}

	private function requestCURL($resource) {
		$con = curl_init();
		curl_setopt($con, CURLOPT_USERAGENT, G3Client::$USER_AGENT);
		curl_setopt($con, CURLOPT_URL, $resource);
		curl_setopt($con, CURLOPT_COOKIEJAR, tempnam(sys_get_temp_dir(), 'g3client.cookies'));
		curl_setopt($con, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($con, CURLOPT_AUTOREFERER, true);
		curl_setopt($con, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($con, CURLOPT_MAXREDIRS, 16);
		curl_setopt($con, CURLOPT_HTTPHEADER, array(
			'X-Gallery-Request-Method: get',
			'X-Gallery-Request-Key: ' . urlencode($this->apiKey))
		);

		$content = curl_exec($con);
		$response = curl_getinfo($con);

		$result = false;

		if($response['http_code'] == 200) {
			$result = json_decode($content);
			$this->requestCache[$resource] = $result;
		} else {
			$http_codes = parse_ini_file(dirname(__FILE__) . '/http_codes.ini');

			$result = array(
				'failure' => true,
				'http_status' => $response['http_code'],
				'msg' => $http_codes[$response['http_code']]
			);
		}

		return $result;
	}

	private function requestHTTP2($resource) {

		$request = new HTTP_Request2($resource, HTTP_Request2::METHOD_GET, array(
			'follow_redirects' => true,
			'max_redirects' => 12,
			'ssl_verify_peer' => false,
		));

		$request->setHeader('User-Agent', G3Client::$USER_AGENT);
		$request->setHeader('X-Gallery-Request-Method', 'get');
		$request->setHeader('X-Gallery-Request-Key', $this->apiKey);

		$result = false;

		try {
			$response = $request->send();

			if ($response->getStatus() == 200) {
				$result = json_decode($response->getBody());
				$this->requestCache[$resource] = $result;
			} else {
				$result = array(
					'failure' => true,
					'http_status' => $response->getStatus(),
					'msg' => $response->getReasonPhrase()
				);
			}
		} catch(HTTP_Request2_Exception $e) {
			$result = array(
				'failure' => true,
				'msg' => $e->getMessage()
			);
		}

		return $result;
	}

	public function getItem($parentId = -1, $type = '') {
		$data = $this->request('item/' . (($parentId == -1) ? '1' : $parentId) . (!empty($type) ? '?type=' . $type : ''));

		if(!is_object($data)) return $data;

		$members = $this->extractMembers($data);

		$photos = array();
		$albums = array();

		foreach($members as $member) {
			$album = $this->toAlbum($member);

			if(!$album) {
				$photos[] = $this->toPhoto($member);
			} else {
				$albums[] = $album;
			}
		}

		return array(
			'curitem' => $this->toArray($data),
			'parents' => $this->getParents($data),
			'albums' => $albums,
			'photos' => $photos
		);
	}

    /**
     * Returns a random photo
     *
     * @param parentId the id of the parent
     * @param scope the scope of the query (all|direct)
     * @return a random photo or false, if the request fails
     */
    public function getRandomPhoto($parentId = 1, $scope = 'all') {
        $data = $this->request('item/' . $parentId . '?scope=' . $scope . '&random=true&type=photo');
        if(!is_array($data)) {
            $result = $this->extractMembers($data);
            $result = $this->toRandomPhoto($result);
        }

        return $result;
    }

	private function extractMembers($data, $type = false) {
		if(!isset($data->members))
			return array();

		$result = array();

		foreach($data->members as $member) {
			$memberData = $this->request($member, true);

			if($type == false || ($type != false && $memberData->type == $type)) {
				$result[] = $memberData;
			}
		}

		return $result;
	}

	private function toAlbum($item) {
		return $this->toArray($item, 'album');
	}

	private function toPhoto($item) {
		return $this->toArray($item, 'photo');
	}

    private function toRandomPhoto($item) {
        return $this->toArray($item, 'random');
    }

	private function toArray($item, $type = '') {
	    if($type == 'random' && is_array($item) && isset($item[0])) {
	        $item = $item[0];
	        $type = 'photo';
	    }

		if(!is_object($item) || !isset($item->entity) || !isset($item->entity->type) ||
			(!empty($type) && ($item->entity->type != $type))) return array();

		$result = $this->getCommonData($item, $type);

		if($item->entity->type == 'photo') {
			$result['imgurl'] = $item->entity->resize_url_public;
			$result['img_height'] = $item->entity->resize_height;
			$result['img_width'] = $item->entity->resize_width;

			$result['thumb_height'] = $item->entity->thumb_height;
			$result['thumb_width'] = $item->entity->thumb_width;

			$result['fileurl'] = $item->entity->file_url;
			$result['g3url'] = $item->entity->web_url;
		}

		return $result;
	}

	private function getCommonData($item) {
		if(!is_object($item)) return array();

		return array(
			'id' => $item->entity->id,
			'type' => $item->entity->type,
			'title' => $item->entity->title,
			'slug' => $item->entity->slug,
			'created' => $item->entity->created,
			'updated' => $item->entity->updated,
			'viewcount' => $item->entity->view_count,
			'thumb' => $item->entity->thumb_url_public,
			'children' => (isset($item->members) ? count($item->members) : 0),
			'apiurl' => isset($item->url) ? $item->url : false,
		);
	}

	private function getParents($item) {
		if(empty($item) || !isset($item->entity)) return array();

		$result = array();

		$curItem = $item->entity;
		while(!empty($curItem) && isset($curItem->parent)) {
			$curParent = $this->request($curItem->parent, true);

			$result[] = $this->toArray($curParent);

			$curItem = $curParent->entity;
		}

		return $result;
	}

}
?>
