<?php

/**
* Auto load page data
**/

class Flatfile_View_Page extends Flatfile_View_App{

	public $page;

	public function __construct()
	{
		// Load Flatfile
		$this->page = new Model_Page(Request::initial()->param('slug'));
		// echo Debug::vars($this->page);
	}
}