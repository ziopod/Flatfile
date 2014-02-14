<?php

/**
* Auto load page data
**/

class Flatfile_View_Page extends View_App{

	/**
	* @var	Flafil object
	**/
	public $page;

	public function __construct()
	{
		// Try to load Flatfile
		try
		{
			$this->page = new Model_Page(Request::initial()->param('slug'));		
		}
		catch (Kohana_Exception $e)
		{
			throw HTTP_Exception::factory(404, __("Unable to find URI :uri"), array(':uri' => Request::initial()->uri()	));		
		}

		$this->title = $this->page->title;
		// $this->page->content;
		// echo Debug::vars($this->page);
	}
}