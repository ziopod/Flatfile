<?php
/**
* Page d'erreur 404
*
* @package		AMAP
* @category		View Model
*/

class View_Error extends View_App{

	/**
	* La page statique à afficher
	**/
	public $page;

	/**
	* @vars Classes CSS personnalisées
	**/
	public $custom_css ='error_page ';

	public function set_error_code($error_code)
	{
		$this->error_code = $error_code;
		// $this->page = 'errors/'.$error_code;
		$this->error = Flatfile::factory('Error', $error_code);
		$this->title = $this->error->title;
	}

}