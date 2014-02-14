<?php defined('SYSPATH') OR die ('No direct script access');

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
			$this->response->boody('No template found for View_' . Request::initial()->controller() . '_' . Request::initial()->action() . '!');
		}
	}
}