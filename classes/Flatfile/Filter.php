<?php defined('SYSPATH') OR die ('No direct script access');

/**
* Filters for metas data
*
* @package		Flatfile
* @author		Ziopod <ziopod@gmail.com>
* @copyright	(c) 2016-2017 Ziopod
* @license		http://opensource.org/licenses/MIT
**/

class Flatfile_Filter extends Flatfile_Core{

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
		$result = array(
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
			$result['items'][] = $item;
		}

		if ($result['items'])
		{
			$result['load'] = TRUE;
		}

		return $result;
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
		$result = array(
			'load'	=> FALSE,
			'items'	=> array(),
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

			$result['items'][] = $new_item;
		}

		if ($result['items'])
		{
			$result['load'] = TRUE;
		}

		return $result;
	}
}