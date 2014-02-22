<?php defined('SYSPATH') OR die ('No direct script access');
/**
* # Flatfile
*
* Boilerplate drafting structure
*
* @package		Flatfile
* @author		Ziopod <ziopod@gmail.com>
* @copyright	(c) 2013-2014 Ziopod
* @license		http://opensource.org/licenses/MIT
**/

class Flatfile_Core {
	
	/**
	* @var	string	Type of Flatfile Model
	**/
	protected $_type;

	/**
	* @var	string	Path to the folder which contain files
	*/
	protected $_path;

	/**
	* @var	string	Markdown filename
	**/
	protected $_filename;

	/**
	* @var	string	Slug
	**/
	protected $_slug;

	/**
	* @var	array	For storing file data
	**/
	protected $_data = array();

	/**
	* @var	string	Filename
	**/
	public $filename;

	const METAS_SEPARATOR = '---';
	const CONTENT_HEADLINE_SEPARATOR = '<!--more-->';

	/**
	* Return a model of the model na provided. You can spacify an file 
	* slug (file name base) for load or create o specifque file model
	*
	*		$post = Flatfile::factory('Post', 'my-lovely-post');
	*
	* @chainable
	* @param	string	The model name
	* @param	string	A specific slug
	*
	* @return	object
	*/	
	public static function factory($model, $slug = NULL)
	{
		$model = 'Model_' . ucfirst($model);
		return new $model($slug);
	}


	/**
	* Construct empty model or try to fetch specific file when slug are provided
	*
	*		$post = new Post_Model('my-lovely-post');
	*
	* @param	string	A specific slug
	*/
	public function __construct($slug = NULL)
	{
		// Get subfolders
		$sub_folders = explode('/', $slug);
		// Get slug part
		$slug = array_pop($sub_folders);
		// Store subfolders
		if (! empty($sub_folders))
			$sub_folders = implode(DIRECTORY_SEPARATOR, $sub_folders);

		// Store type
		$folder = NULL;
		$classname = get_class($this);
		if ($classname !== 'Flatfile')
		{
			$this->_type = strtolower(substr($classname, 6));		
			$folder = Inflector::plural($this->_type) . DIRECTORY_SEPARATOR;
		}

		// Store folder path
		$this->_path = DOCROOT . 'content' . DIRECTORY_SEPARATOR . $folder;

		if ($sub_folders)
			$this->_path .= $sub_folder . DIRECTORY_SEPARATOR;

		// Trying to load Flatile if slug is provided
		if ($slug !== NULL)
		{
			$this->_slug = $slug;
			$this->slug = $slug; // Store slug in _data array
			$this->_filename = $slug . '.md';
			// A slug spécified, automaticly find file
			$this->find();
		}

	}

	// Return headline
	public function headline()
	{
		return $this->headline;
	}

	// Return content
	public function content()
	{
		return $this->content;
	}
	// public function content()
	// {
	// 	$this->__set('content', $this->content);
	// }


	/**
	* Define filter
	* **Inspire by Kohana ORM::run_filter()**
	**/
	public function filters()
	{
		return array();
	}

	/**
	* String to list filter
	*
	* @param	string	Example: ""
	* @return	array 
	**/
	public static function str_to_list($str)
	{
		$tags = array();

		foreach (explode(', ', $str) as $tag)
		{
			$tags[] = array(
				'name'	=> $tag,
				'slug'	=> URL::title($tag),
			);
		}

		return $tags;
	}

	// Test with Tumblr api: http://api.tumblr.com/v2/blog/blogdamientran.tumblr.com/posts?id=76514425378&api_key=fuiKNFp9vQFvjLNvx4sUwti4Yb5yGutBN4Xh10LXZhhRKjWlV4
	/**
	* Json api result form URL
	**/
	public static function api()
	{

	}

	/**
	* Find file an load data
	**/
	public function find()
	{
		// Constant STATE_LOADING
		// Try to load file data
		$this->_load();
	}

	/**
	* Load data
	**/
	protected function _load($multiple = FALSE)
	{
		if ($multiple === TRUE)
		{

		}

		if ($this->_slug)
		{
			// Strore filename
			$this->filename = $this->_get_files($this->_slug);

			if ($this->filename)
			{
				// Parse meta data from markdown file
				$this->_parse_meta();
			}
			else
			{
				// Throw exception, Unable to find markdown file
				throw new Kohana_Exception(__("Unable to find :filename in :folder", array(':filename' => $this->_filename, ':folder' => $this->_path))); exit;
			}
		}
		else
		{
			// Throw exception, No slug secified
		}
	}

	/**
	* Retreive all files or one file base on slug
	**/
	// Use directory iterator
	// http://us2.php.net/manual/fr/class.directoryiterator.php
	protected function _get_files($slug = NULL)
	{
		// Get markdown file by slug
		if ($slug !== NULL)
			if (is_file($this->_path . $this->_filename))
				return 	$this->_path . $this->_filename;

		return false;
	}


	/**
	* Parse meta data from Markdown file
	**/
	protected function _parse_meta()
	{
		// Open file in read mode
		// $file = fopen(Kohana::find_file('content', $this->_path . $this->_slug, 'md'), 'r');
		$file = fopen($this->_path . $this->_filename, 'r');

		// Scan each line
		while ($line = fgets($file))
		{
			// Ending metas zone, load content
			if (strpos($line, Flatfile::METAS_SEPARATOR) !== FALSE)
				break;

			if (($index = strpos($line, ':')) !== FALSE) // Get new property
			{
				$property = strtolower(substr($line, 0, $index));

				// Preserve slug
				if ($property == 'slug')
					continue;

				// Inititate new property
				$this->$property = NULL;
				// Get first value part
				$newline = substr($line, $index + 1);
			}
			else // Store value
			{
				// Preserve slug
				// TODO: tester si necessaire
				// if ($property == 'slug')
				// 	continue;

				// Adding value
				$newline .= $line;
			}

			// Store property value
			$this->$property = trim($newline);
		}

		// Close file
		fclose($file);
	}

	/**
	* Parse content from Markdown file
	**/
	protected function _parse_content()
	{
		$file = Kohana::find_file('content', $this->_path . $this->_slug, 'md');
		$file = $this->_path . $this->_filename;
		$skip_metas = TRUE;
		$headline = ''; // Flatfile::CONTENT_HEADLINE_SEPARATOR = <!--more-->
		$content = '';

		foreach (new SplFileObject($file) as $line)
		{
			if ($skip_metas)
			{
				if (strpos($line, Flatfile::METAS_SEPARATOR) !== FALSE)
					$skip_metas = FALSE;
			}
			else
			{

				if (strpos($line, Flatfile::CONTENT_HEADLINE_SEPARATOR) !== FALSE)
				{
					$headline = $content;
					$content = '';
				}
				else
				{
					$content .= $line;				
				}
			}
		}

		// if ($headline)
		// {}
		$this->headline = $headline;
		$this->content = $content; // Trim ?

	}

	/**
	* Filters a value for a specific property
	* **Inspire by Kohana ORM::run_filter()**
	*
	* @param	string	Property name
	* @param	string	Value to filter
	**/
	protected function _run_filter($property, $value)
	{
		$filters = $this->filters();
		// Get filters fot this property
		$wildcards = empty($filters[TRUE]) ? array() : $filters[TRUE];
		// Merge in the wildcards
		$filters = empty($filters[$property]) ? $wildcards : array_merge($wildcards, $filters[$property]);
		// Bind the property an model
		$_bound = array(
			':property'	=> $property,
			':model'	=> $this,
		);

		foreach ($filters as $array)
		{
			$_bound[':value'] = $value;
			$filter = $array[0];
			$params= Arr::get($array, 1, array(':value'));

			foreach ($params as $key => $param)
			{
				if (is_string($param) AND array_key_exists($param, $_bound))
					// Replace with bound value
					$params[$key] = $_bound[$param];
			}

			if (is_array($filter) OR ! is_string($filter))
			{
				// Callback as an array or a lambda
				$value = call_user_func_array($filter, $params);
			}
			elseif (strpos($filter, '::') === FALSE)
			{
				// Use a function call
				$function = new ReflectionFunction($filter);
				// Call $function($this[$property], $param, …) With Reflection
				$value = $function->invokeArgs($params);
			}
			else
			{
				// Split the class and method of the rule
				list($class, $method) = explode('::', $filter, 2);
				// Use static method call
				$method = new ReflectionMethod($class, $method);
				// Class $class::$method($this[$property], $param, …) with Reflection
				$value = $method->invokeArgs(NULL, $params);
			}
		}

		return $value;
	}

	/**
	* Set value in data array
	*
	* @return	void
	**/
	public function __set($key, $value)
	{
		// Call _run_filter
		$value = $this->_run_filter($key, $value);
		$this->_data[$key] = $value;
	}

	/**
	* Try to get a value from data array
	*
	* @throws	Flatfile_Exception
	* @param	string	Key of data array
	* @return	mixte
	**/
	public function __get($key)
	{

		// if ($key === 'content' AND $this->_loaded)
		if ($key === 'content' OR $key === 'headline')
			$this->_parse_content();

		if (array_key_exists($key, $this->_data))
			return $this->_data[$key];

		return NULL;
		// throw new Flatfile_Exception('Property ' . $key . ' does not exist in ' . get_class($this) . ' !');
	}

	/**
	* Magic isset to test _data
	**/
	public function __isset($key)
	{
		// STATE_LOADED
		if ($this->_loaded !== TRUE)
			$this->find();

		return isset($this->_data[$key]);
	}

}