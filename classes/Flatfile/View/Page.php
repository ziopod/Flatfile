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
		// Load Flatfile
		$this->page = new Model_Page(Request::initial()->param('slug'));
		$this->title = $this->page->title;
		// $this->page->content;
		// echo Debug::vars($this->page);
	}
}