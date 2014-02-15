<?php defined('SYSPATH') OR die('No direct script access.');

/**
* You can replace this class with your own stuffs
*
* @package		Flatfile
* @category		Controller
* @author		Ziopod <ziopod@gmail.com>
* @copyright	(c) 2013-2014 Ziopod
* @license		http://opensource.org/licenses/MIT
**/

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