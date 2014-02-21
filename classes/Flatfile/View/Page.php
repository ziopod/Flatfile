<?php

/**
* # Page View Model
*
* Automaticly load page based on slug, throw 404 if page doesn't exist.
*
* @package		Flatfile
* @category		View Model
* @author		Ziopod <ziopod@gmail.com>
* @copyright	(c) 2013-2014 Ziopod
* @license		http://opensource.org/licenses/MIT
**/

class Flatfile_View_Page extends View_App{

	/**
	* @var	object	Flafile page object
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

		// HTML meta data
		if ($this->page->title)
			$this->title = $this->page->title;
		
		$this->author = $this->page->author;
		$this->license = $this->page->license;
		$this->description = $this->page->description;
	}
}