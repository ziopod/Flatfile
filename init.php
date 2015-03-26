<?php defined('SYSPATH') or die('No direct script access.');

/**
* Autoload page example
**/
// Route::set('page', '<slug>', array(
// 		// 'slug'	=> 'my_page', // restrict a specific url
// 		// 'slug'	=> '.*', // for any extension in url
//		// 'slug'	=> '[a-zA-Z0-9_/]+', // for subfolder
// 	))
// 	->defaults(array(
// 		'controller'	=> 'App',
// 		'action'		=> 'page',
// 	));
	
/**
* Default Flatfile route whit specific Home Action example
**/
// Route::set('default', '(<controller>(/<action>(/<id>)))')
// 	->defaults(array(
// 		'controller'	=> 'App',
// 		'action'		=> 'page',
// 		'slug'			=> 'home',
// 	));

// Load Smartypants Typographer (old school style)
include Kohana::find_file('vendor', 'smartypants-typographer/smartypants', 'php');