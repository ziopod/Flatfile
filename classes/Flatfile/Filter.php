<?php defined('SYSPATH') OR die ('No direct script access');

/**
* Filters for metas data
*
* @package		Flatfile
* @author		Ziopod <ziopod@gmail.com>
* @copyright	(c) 2016-2017 Ziopod
* @license		http://opensource.org/licenses/MIT
**/

class Flatfile_Filter extends Flatfile_Core {

	/**
	* Store result
	* @var	array
	**/
	protected static $_cache = array();

	/**
	* Transform string to array of tags
	* Elements must be separate by colon and one space `,`
	*
	* Example : 
	* 
	*	tags: Choux rouges, Patates
	*
	*	tags = array(
	*		array(
	*			'name'	=> 'Choux rouges'
	*			'slug'	=> 'choux-rouge'
	*			'url'	=> 'http://localhost/tag/choux-rouge'
	*		),
	*		array(
	*			'name'	=> 'Patates'
	*			'slug'	=> 'patate'
	*			'url'	=> 'http://localhost/tag/patate'
	*		),
	*	);
	* 
	*
	* @param	string	String of elements, eg. Patates, Choux rouges, Tomates
	* @param	mixte	TRUE | NULL | string. Default base url, none or specific base url
	* @return	array	Array of elements
	**/
	public static function tags($value, $base_url = FALSE)
	{
		$cache_key = md5($value);

		if (Arr::get(self::$_cache, $cache_key))
		{
			return self::$_cache[$cache_key];
		}

		self::$_cache[$cache_key] = array(
			'load'	=> FALSE,
			'items'	=> array(),
		);

		$base_url = ($base_url) ? $base_url : URL::base(TRUE, TRUE) . 'tag/';

		foreach (explode(', ', $value) as $item)
		{
			$item = array(
				'name'	=> $item,
				'slug'	=> URL::title($item, '-', TRUE),
			);
			$item['url'] = $base_url . $item['slug'];
			self::$_cache[$cache_key]['items'][] = $item;
		}

		if (self::$_cache[$cache_key]['items'])
		{
			self::$_cache[$cache_key]['load'] = TRUE;
		}

		return self::$_cache[$cache_key];
	}

	/**
	* Transform multiples terms and elements to an multi dimentionnal array.
	*
	* Example : 
	*
	*	articles:
	*	 - ref: 54837
	*	   price: 3.5
	*	   name: Machin
	*	 - ref: 16375
	*	   price: 9.99
	*	   name: Truc
	*
	* @param	string		String of elements
	* @return	array		Multi-dimentionnal array
	**/
	public static function items($value)
	{
		$cache_key = md5($value);

		if (Arr::get(self::$_cache, $cache_key))
		{
			return self::$_cache[$cache_key];
		}

		self::$_cache[$cache_key] = array(
			'load'		=> FALSE,
			'items'	=> array()
		);

		foreach (explode('-', $value) as $items)
		{
			$new_item = array();

			if ( ! trim($items))
				continue;

			foreach (explode("\n", $items) as $key_value)
			{
				if ( ! trim($key_value))
					continue;

				$item = explode(':', $key_value);
				$new_item[trim($item[0])] = trim($item[1]);
			}

			self::$_cache[$cache_key]['items'][] = $new_item;
		}

		if (self::$_cache[$cache_key]['items'])
		{
			self::$_cache[$cache_key]['load'] = TRUE;
		}

		return self::$_cache[$cache_key];
	}

	// https://api.tumblr.com/v2/blog/blogdamientran.tumblr.com/info?api_key=fuiKNFp9vQFvjLNvx4sUwti4Yb5yGutBN4Xh10LXZhhRKjWlV4
	/**
	* Json api result form URL
	*
	* @param	string		API url
	* @return	array		API response
	**/
	public static function api($value)
	{
		$cache_key = md5($value);

		if (Arr::get(self::$_cache, $cache_key))
		{
			return self::$_cache[$cache_key];
		}

		self::$_cache[$cache_key] = array(
			'load'	=> FALSE,
		);
		$request = Request::factory($value);

		try
		{
			$request = Request::factory($request->uri())
				->query($request->query())
				->execute()->body();
			$request = json_decode($request);
			self::$_cache[$cache_key] = array(
				'load'		=> $request->meta->status === 200,
				'meta'		=> $request->meta,
				'response'	=> $request->response,
			);
		}
		catch (Request_Exception $e)
		{
			self::$_cache[$cache_key] =  array('error' => $e->getMessage());
			return self::$_cache[$cache_key];
		}

		return self::$_cache[$cache_key];
	}

	/**
	* Markdown formatting
	*
	* @param	string		Markdown text
	* @param	object		Markdown engine (wip)
	* @return	string		HTML formatted text
	**/
	public static function markdown($value, $engine = NULL)
	{
		$cache_key = md5($value);

		if (Arr::get(self::$_cache, $cache_key))
		{
			return self::$_cache[$cache_key];
		}

		self::$_cache[$cache_key] = trim(Michelf\MarkdownExtra::defaultTransform($value));
		return self::$_cache[$cache_key];
	}

	/**
	* Load Flatfiles
	*
	* @param	string		List of slugs
	* @param	string		Model name
	* @return	mixte 		Flatfile object or array of Flatfiles objects
	**/
	public static function flatfile($value, $model = NULL)
	{
		$result = array();

		foreach (explode(', ', $value) as $key => $slug)
		{
			try
			{
				$model = Flatfile::factory('Author', URL::title($slug));

				if ($model->loaded())
				{
					$result[] = $model;
				}
			}
			catch (Kohana_Exception $e)
			{
				Log::instance()->add(Log::WARNING, $e->getMessage());
			}
		}

		return $result;
	}

}