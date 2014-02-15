<?php defined('SYSPATH') or die ('No direct script access.');
/**
* Error model
*
* **Evaluate before adding to flatfile module core**
*
* @package		Flatfile
* @category		Evaluate
**/

class Model_Error extends Flatfile{
	
	/**
	* Filters
	**/
	public function filters()
	{
		return array(
			'content' => array(
				array('Markdown'),
				array('SmartyPants'),
			),
		);
	}

	/**
	* Retourne la titre
	**/
	public function title()
	{
		return $this->title;
	}

	public function headline()
	{
		$more_pos = strpos($this->content, '<!--more-->');
		return substr($this->content, 0, $more_pos);
	}

	public function content()
	{
		$more_pos = strpos($this->content, '<!--more-->');
		return substr($this->content, $more_pos);
	}
}