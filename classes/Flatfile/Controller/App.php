<?php defined('SYSPATH') OR die ('No direct script access');
/**
* # App
*
* This basic controller provide autorendering layout hooks
*
* Provide default action for :
*
* - pages
* - posts (comming soon)
*
* @package		Flatfile
* @category		Controller
* @author		Ziopod <ziopod@gmail.com>
* @copyright	(c) 2013-2014 Ziopod
* @license		http://opensource.org/licenses/MIT
**/

class Flatfile_Controller_App extends Controller {

	/**
	* @var Default layout
	**/
	public $layout;

	/**
	* @var View to render
	**/
	public $view;

	/**
	* Standard page
	**/
	public function action_page()
	{
		$this->view = new View_Page;
	}

	/**
	* Hooks
	**/
	public function before()
	{
		parent::before();
		// Init default layout
		$this->layout = Kostache_Layout::factory('layout/default');
		$view = 'View_' . ucfirst(Request::initial()->controller());
		$this->view = new $view;
	}

	public function after()
	{
		parent::after();
		
		// Auto render view
		if (isset($this->view))
		{
			$this->response->body($this->layout->render($this->view));
		}
		else
		{
			$this->response->body('No template found for View_' . Request::initial()->controller() . '_' . Request::initial()->action() . '!');
		}
	}
}