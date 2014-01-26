<?php

class Flatfile_View_App {

	/**
	* @var string	Title
	**/
	public $title = "Welcome";

	/**
	* Return stylesheet list
	*
	* @return  array
	**/
	public function styles()
	{
		return array(
			array(
				'src'	=> $this->base_url() . 'css/style.css',
				'media'	=> 'screen',
			),
		);
	}

	/**
	* Return scritps list
	*
	* @return array
	**/
	public function scripts()
	{
		return array(
			// array(
			// 	'src' => $this->base_url() . 'js/scritps.js',
			// ),
		);
	}

	/**
	* Return main navigation
	*
	* @return 	array
	**/
	public function navigation()
	{
		return array(
			array(
				'url'		=> $this->base_url(),
				'name'		=> __('Home'),
				'title'		=> __('Go to Home'),
				'current'	=> Request::initial()->controller() === 'App' AND Request::initial()->action() === 'home',
			),
			array(
				'url'		=> $this->base_url() . 'about',
				'name'		=> __('Example page'),
				'title'		=> __('Go to example page'),
				'current'	=> Request::initial()->controller() === 'App' AND Request::initial()->action() === 'about',
			),
		);
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