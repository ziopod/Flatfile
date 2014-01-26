<?php defined('SYSPATH') OR die ('No direct script access');

class Flatfile_Model_Page extends Flatfile {

	/**
	* Apply filter on data
	**/
	public function filters()
	{
		return array(
			'excerpt' => array(
				array('Markdown'),
				array('SmartyPants'),
			),
			'headline' => array(
				array('Markdown'),
				array('SmartyPants'),
			),
			'content' => array(
				array('Markdown'),
				array('SmartyPants'),
			),
		);
	}
	

	/**
	* Return specifics data
	**/
	public function url()
	{
		return URL::base(TRUE, TRUE) . 'post/' . $this->slug();
	}


	// TODO : supprimer
	// public function content()
	// {
	// 	return $this->content;
	// }

	// public function title()
	// {
	// 	return $this->title;
	// }


}