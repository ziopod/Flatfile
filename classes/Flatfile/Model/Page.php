<?php defined('SYSPATH') OR die ('No direct script access');
/**
* # Page
*
* Model for page content
*
* @package		Flatfile
* @category		Model
* @author		Ziopod <ziopod@gmail.com>
* @copyright	(c) 2013-2014 Ziopod
* @license		http://opensource.org/licenses/MIT
**/

class Flatfile_Model_Page extends Flatfile {

	/**
	* Apply filter on data
	**/
	public function filters()
	{
		return array(
			'excerpt' => array(
				array('Flatfile::Markdown'),
				array('SmartyPants'),
			),
			'headline' => array(
				array('Flatfile::Markdown'),
				array('SmartyPants'),
			),
			'content' => array(
				array('Flatfile::Markdown'),
				array('SmartyPants'),
			),
			'credit' => array(
				array('json_decode'),
			),
			'license' => array(
				array('json_decode'),
			),
		);
	}
	

	/**
	* Return specifics data
	**/
	public function url()
	{
		return URL::base(TRUE, TRUE) . $this->slug;
	}

}