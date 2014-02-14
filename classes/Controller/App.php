<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_App extends Flatfile_Controller_App {

	public function before()
	{
		parent::before();
		/** Uncomment for set maintenance mode **/
		// throw HTTP_Exception::factory(503, __("Our site is down for maintenance, please back in :time"), array(':time' => 'few minutes'));		
	}

	/**
	* Home (example)
	**/
	public function action_home()
	{
		$this->view = new View_Home;
	}

}