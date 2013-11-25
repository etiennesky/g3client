<?php

require_once( ABSPATH . WPINC . '/registration.php');

class gallery3Proxy {
	function requestHandler()
	{
		switch($_GET['what'])
		{
			case 'tree':
				$tree = gallery3Proxy::queryTree();
				header("Content-Type: application/json;charset=utf-8;");
				echo json_encode($tree);
				exit;
				
			case 'photos':
				$photos = gallery3Proxy::queryPhotos($_GET['node']);
				header("Content-Type: application/json;charset=utf-8;");
				echo json_encode($photos);
				exit;
			
			case 'fetch':
				$result = gallery3Proxy::getGalleryImage($_GET['photo'],$_GET['post']);
				header("Content-Type: application/json;charset=utf-8;");
				echo json_encode(array('id' => $result));
				exit;
				
			case 'meta':	
				$item = gallery3Proxy::getNodeMeta($_GET['node']);
				header("Content-Type: application/json;charset=utf-8;");
				echo json_encode($item);
				exit;				
				
			case 'proxy':
				$result = gallery3Proxy::getThumbnail($_GET['node']);
				
				header("Content-Type: " . $result['mime']);
				echo $result['data'];
				exit;
		}
	}
	
	function getNodeMeta($node)
	{
		$node = intval($node);
		$return = array();
		$sizes = array();
		
		$item = gallery3Proxy::getRest('item/' . $node . '?type=photo');
		$ent = $item->{'entity'};
		
		// Thumbnail public?
		$size = array(
			'height' => $ent->{'thumb_height'},
			'width' => $ent->{'thumb_width'},
			'name' => 'Thumbnail',
			'value' => 'thumb',
			'public' => false
		);
		if (isset($ent->{'thumb_url_public'}))
		{
			$size['public'] = true;
			$size['url'] = $ent->{'thumb_url_public'};
		}
		$sizes[] = $size;
		
		// Resize image public?
		$size = array(
			'height' => $ent->{'resize_height'},
			'width' => $ent->{'resize_width'},
			'name' => 'Resize',
			'value' => 'resize',
			'public' => false
		);
		if (isset($ent->{'resize_url_public'}))
		{
			$size['public'] = true;
			$size['url'] = $ent->{'resize_url_public'};
		}
		$sizes[] = $size;
		
		// Full size image public?
		$size = array(
			'height' => $ent->{'height'},
			'width' => $ent->{'width'},
			'name' => 'Full',
			'value' => 'full',
			'public' => false
		);
		if (isset($ent->{'resize_url_public'}))
		{
			$size['public'] = true;
			$size['url'] = $ent->{'file_url_public'};
		}
		$sizes[] = $size;
		
		$return['thumbnail'] = array(
			'url' => gallery3Proxy::getThumbByEntity($ent),
			'width' => $ent->{'thumb_width'},
			'height' => $ent->{'thumb_height'}
		);
		$return['title'] = $ent->{'title'};
		$return['description'] = $ent->{'description'};
		$return['views'] = $ent->{'view_count'};
		$return['filename'] = $ent->{'name'};
		$return['sizeList'] = $sizes;
		$return['url'] = $ent->{'web_url'};
		
		$size = $ent->{'file_size'};
		if ($size == '') { $size = $ent->{'resize_size'}; }
		
		if ($size < 1024) { $return['size'] = $size . ' Bytes'; }
		else if ($size < 1048576) { $return['size'] = round( $size / 1024, 2) . ' KB'; }
		else { $sizeStr = round( $return['size'] / 1048576, 2) . ' MB'; }
		
		return $return;
	}
	
	function queryPhotos($node)
	{
		$options = get_option('gallery3_picker_options');
		if ($options['gallery3_query_type'] == 'tree')
		{
			$photoList = gallery3Proxy::getRest('tree/' . $node . '?type=photo&depth=1')->{'entity'};
		}
		else
		{
			$album = gallery3Proxy::getRest('item/' . $node . '?type=photo');
			
			$photos = array();
			foreach ($album->{'members'} as $value)
			{
				$photos[] = '"' . $value . '"';
			}
			
			$photoList = gallery3Proxy::getRestChunked('items?type=photo&', $photos);
		}
		
		$return = array();
		foreach ($photoList as $item)
		{
			if ($item->{'entity'}->{'type'} == 'album') { continue; }
			$thumbUrl = gallery3Proxy::getThumbByEntity($item->{'entity'});
			$return[] = array(
				'id' => $item->{'entity'}->{'id'},
				'thumb' => $thumbUrl
			);
		}
		return $return;
	}
	
	function getThumbByEntity($entity)
	{
		if (isset($entity->{'thumb_url_public'}))
		{
			return $entity->{'thumb_url_public'};
		}
		
		return admin_url("admin-ajax.php?what=proxy&node=" . $entity->{'id'} . "&action=gallery3proxy");
	}
	
	function queryTree($origin = null)
	{
		$options = get_option('gallery3_picker_options');
		$g3p_cache = array();
		$root = $options['gallery3_url'] . 'rest/item/1';
		
		if ($options['gallery3_query_type'] == 'tree')
		{
			$itemList = gallery3Proxy::getRest('tree/1?type=album');
			foreach ($itemList->entity as $item)
			{
				$g3p_cache[ $item->{'url'} ] = $item;
			}
			
			/* Fake a child list if we don't get any (which we won't in stock 3.0.1 G3) */
			foreach ($g3p_cache as $key => $item)
			{
				$parent = (string)$item->{'entity'}->{'parent'};
				if ($parent != '') { $g3p_cache[$parent]->{'members'}[] = $key; }
			}		
		}
		else
		{
			/*
				First request, fetch all albums
			*/
			$item = gallery3Proxy::getRest('item/1?type=album&scope=all');
			
			/* 
				Second request, fetch album metadata
				
				Gallery3 3.0.0 has a bug pertaining to this, so the members 
				collections we get here contain photos as well. Not so much fun.
			*/
			$albumList = array('"' . $root . '"');
			foreach ($item->{'members'} as $album)
			{
				$albumList[] = '"' . $album . '"';
			}
			
			
			$itemList = gallery3Proxy::getRestChunked('items?type=album&', $albumList);
			foreach ($itemList as $item)
			{
				$g3p_cache[ $item->{'url'} ] = $item;
			}
		}
		
		return gallery3Proxy::treeBuilder($root, $g3p_cache);
	}
	
	function treeBuilder($itemUrl, $g3p_cache)
	{
		$item = $g3p_cache[$itemUrl];
		$morsel = array(
			'data' => $item->{'entity'}->{'title'},
			'attr' => array( 'id' => 'g3pt_' . $item->{'entity'}->{'id'} )
		);
		
		$albums = array();
		
		if (isset($item->{members}))
		{
			foreach ($item->{members} as $value)
			{
				/*
					There seems to exist a bug in Gallery3 3.0.0
					The following requests should (according to the docs) yield the 
					same result:
					
					/rest/item/x?type=album 
					/rest/items?urls["url-to-x"]&type=album
					
					However, they don't - so we get the entire list of non-album contents 
					as well, even if we don't want it. Thankfully, we already have an 
					all inclusive list of albums from the first request we did in 
					queryTree, so we use that to sieve out the non-album members...
				*/
				
				if (array_key_exists($value, $g3p_cache))
				{
					$children = gallery3Proxy::treeBuilder($value, $g3p_cache);
					if (count($children) > 0) {
						$morsel['children'][] = $children;
					}
				}
			}
		}
		
		$tree[] = $morsel;
		return $tree;
	}
	
	function getRestChunked($requestBase, $urls)
	{
		$options = get_option('gallery3_picker_options');
		$maxlen = $options['gallery3_maxlength'];
		if ($maxlen == '') { $maxlen = 4000; }
		
		$request = $requestBase . 'urls=[';
		$chunkList = array();
		
		foreach ($urls as $url)
		{
			if (strlen($request . $url) > $maxlen)
			{
				$request = substr($request, 0, -1) . ']';
				$chunk = gallery3Proxy::getRest($request, true);
				$chunk = substr($chunk, 1, -1); // Remove []
				$chunkList[] = $chunk;
				
				$request = $requestBase . 'urls=[';
			}
			
			$request .= $url . ',';
		}
		
		$request = substr($request, 0, -1) . ']';
		$chunk = gallery3Proxy::getRest($request, true);
		$chunk = substr($chunk, 1, -1);
		if ($chunk != '') { $chunkList[] = $chunk; }
		
		$return = '[' . join(',', $chunkList) . ']';
		return json_decode($return);		
	}
	
	function getRest($request, $rawText = false)
	{
		$options = get_option('gallery3_picker_options');
		$url = $options['gallery3_url'] . 'rest/' . $request;
		
		$req = curl_init($url);
		curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
		gallery3Proxy::apiCheck($req);
		$response = curl_exec($req);
		$status = curl_getinfo($req, CURLINFO_HTTP_CODE);
		
		curl_close($req);
		if ($rawText == true) { return($response); }
		
		$json = json_decode($response);
		return $json;
	}
	
	function getThumbnail($photo)
	{
		$photo = intval($photo);
		$options = get_option('gallery3_picker_options');
		$url = $options['gallery3_url'] . 'rest/data/' . $photo . '?size=thumb';
		
		$req = curl_init($url);
		curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
		gallery3Proxy::apiCheck($req);
		$response = curl_exec($req);
		$mime = curl_getinfo($req, CURLINFO_CONTENT_TYPE);
		
		curl_close($req);
		
		return(array('mime' => $mime, 'data' => $response));
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
	
	function getGalleryImage($photoId, $postId)
	{
		$options = get_option('gallery3_picker_options');
		$upload_dir = wp_upload_dir();
		
		$rest = gallery3Proxy::getRest('item/' . $photoId);
		$entity = $rest->{'entity'};
		
		$meta = array(
			'url' => $entity->{'file_url'},
			'name' => $entity->{'name'},
			'contentType' => $entity->{'mime_type'},
			'title' => $entity->{'title'},
			'description' => $entity->{'description'}
		);
		
		$meta['file'] = $upload_dir['path'] . '/' . $meta['name'];		
		$handle = fopen($meta['file'],'w');
		$req = curl_init($meta['url']);
		curl_setopt($req, CURLOPT_FILE, $handle);
		gallery3Proxy::apiCheck($req);
		$response = curl_exec($req);
		curl_close($req);
		
		return gallery3Proxy::insert_media($postId, $meta);
	}
	
	function insert_media($postId, $meta)
	{
		$time = current_time('mysql');
		if ( $post = get_post($postId) ) {
			if ( substr( $post->post_date, 0, 4 ) > 0 )
				$time = $post->post_date;
		}
		
		
		// Construct the attachment array
		$attachment = array(
			'post_mime_type' => $meta['contentType'],
			
			/* Wordpress uses this as the filename for some reason.. */
			'guid' => $meta['url'] . '/' . $meta['name'], 
			'post_parent' => $postId,
			'post_name' => $meta['name'],
			'post_title' => $meta['title'],
			'post_content' => $meta['description'],
			'post_status' => 'inherit'
		);
		
		$id = wp_insert_attachment($attachment, $meta['file'], $postId);
		if ( !is_wp_error($id) ) {
			wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $meta['file'] ) );
		}
		
		return $id;
	}
}

?>