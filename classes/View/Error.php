<?php
/**
* Error view
*
* **Evaluate before adding to flatfile module core**
*
* @package		Flatfile
* @category		Evaluate
**/

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

		// Watchdog: Try to load error content in "content/errors/{error_code}.md"
		try
		{
			$this->error = new Model_Error($error_code); //Flatfile::factory('Error', $error_code);
			// $this->page = new Model_Page(Request::initial()->param('slug'));		
		}
		catch (Kohana_Exception $e)
		{
			// Throw basic suitable error status 
			throw HTTP_Exception::factory($error_code);		
		}

		$this->title = $this->error->title;
	}

}