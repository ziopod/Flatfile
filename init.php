<?php defined('SYSPATH') or die('No direct script access.');

/**
* Set environment status based on subdomain name or IP address
**/
$server_name = $_SERVER['SERVER_NAME'];

if (strpos($server_name, 'dev.') !== FALSE OR $_SERVER['SERVER_ADDR'] == '127.0.0.1')
{
	Kohana::$environment = Kohana::DEVELOPMENT;
}
else if (strpos($server_name, 'test.') !== FALSE)
{
	Kohana::$environment = Kohana::TESTING;
}
else if (strpos($server_name, 'stage.') !== FALSE)
{
	Kohana::$environment = Kohana::STAGING;
}
else
{
	Kohana::$environment = Kohana::PRODUCTION;
}

/**
 * Attach a file reader to config. Multiple readers are supported.
 **/
switch (Kohana::$environment) {
	case Kohana::DEVELOPMENT:
		Kohana::$config->attach(new Config_File('config/development'));
		error_reporting(E_ALL);
		break;
	case Kohana::TESTING:
		Kohana::$config->attach(new Config_File('config/testing'));
		error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);
		break;
	case Kohana::STAGING:
		Kohana::$config->attach(new Config_File('config/staging'));
		error_reporting(E_ERROR);
		break;
	default:
		Kohana::$config->attach(new Config_File());
		error_reporting();
		break;
}

/**
* Autoload page
**/
Route::set('page', '<slug>', array(
		/** You can use that for restrict page **/
		// 'slug'	=> 'my_page',
		// 'slug'	=> '.*',
	))
	->defaults(array(
		'controller'	=> 'App',
		'action'		=> 'page',
	));
	
/**
* Default Flatfile route whit specific Home Action
**/
Route::set('default', '(<controller>(/<action>(/<id>)))')
	->defaults(array(
		'controller'	=> 'App',
		'action'		=> 'page',
		'slug'			=> 'home',
	));

// Load Smartypants Typographer
include Kohana::find_file('vendor', 'smartypants-typographer/smartypants', 'php');
// Load Markdown
// include Kohana::find_file('vendor', 'markdown/markdown', 'php');
include Kohana::find_file('vendor', 'markdown/Michelf/MarkdownExtra.inc', 'php');