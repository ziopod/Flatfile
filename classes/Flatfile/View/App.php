<?php
/**
* # Basic view model
*
* Provide initial basic methods
*
* **TODO**: Move examples (like navigation content) outside Flatfile Class
*
* @package		Flatfile
* @category		View Model
* @author		Ziopod <ziopod@gmail.com>
* @copyright	(c) 2013-2014 Ziopod
* @license		http://opensource.org/licenses/MIT
**/

class Flatfile_View_App {

	/**
	* @var string	Title
	**/
	public $title = "Get it simple!";

	/**
	* Stylesheet list
	*
	* Add your style like that :
	*
	*	return array(
	*		array(
	*			'src'	=> $this->base_url() . 'css/style.css',
	*			'media'	=> 'screen',
	*		),
	*	);
	*
	* @return  array
	**/
	public function styles()
	{
		return array();
	}

	/**
	* Scripts list
	*
	* Add your scripts like that:
	*
	*	return array(
	*		array(
	*		 	'src' => $this->base_url() . 'js/scripts.js',
	*		),
	*	);
	*
	* @return array
	**/
	public function scripts()
	{
		return array();
	}

	/**
	* Define main navigation
	*
	* Add your navigation like that:
	*
	*	return array(
	*		array(
	*			'url'		=> $this->base_url(),
	*			'name'		=> __('Home'),
	*			'title'		=> __('Go to Home'),
	*			'current'	=> Request::initial()->controller() === 'App' AND Request::initial()->action() === 'home',
	*		),
	*		array(
	*			'url'		=> $this->base_url() . 'about',
	*			'name'		=> __('Example page'),
	*			'title'		=> __('Go to example page'),
	*			'current'	=> Request::initial()->controller() === 'App' AND Request::initial()->param('slug') === 'about',
	*		),
	*	);
	*
	* @return 	array
	**/
	public function navigation(){
		return array();
	}

	/**
	* Root URL
	*
	* @return	string
	**/
	public function base_url()
	{
		return URL::base(TRUE, TRUE);
	}

	/**
	* Current URL
	*
	* @return	string
	**/
	public function current_url()
	{
		return URL::site(Request::initial()->uri(), TRUE);
	}

	/**
	* Current year
	*
	* @return	string
	**/
	public function current_year()
	{
		return date('Y');
	}

	/**
	* Current lang
	**/
	public function lang()
	{
		return I18n::lang();
	}

}