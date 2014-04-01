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
	* @var array	List of model content files (match by filters and queries)
	**/
	protected $_files = array();

	/**
	* @var	array	For storing file data
	**/
	protected $_data = array();

	const FILE_META_SEPARATOR = '_';
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
			$this->_path .= $sub_folders . DIRECTORY_SEPARATOR;

		// Trying to load Flatile if slug is provided
		if ($slug !== NULL)
		{
			$this->_slug = $slug; // Store slug
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
	public static function json_api($value)
	{
		$request = Request::factory($value);
		$result = Request::factory($request->uri())
			->query($request->query())
			->execute()->body();
	
		return json_decode($result)->response;
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
		// Get files
		$this->_get_files();

		if ($multiple === TRUE)
		{

		}
		else
		{

			// Attempt to load Flatfile
			if ($this->_slug)
			{
				// Attempt get filename corresponding to the slug
				if (isset($this->_files[$this->_slug]))
					$this->_filename = $this->_files[$this->_slug];
			}
			else
			{
				throw new Kohana_Exception("No slug specified, please check defaults Route settings");
			}

			if ( ! $this->_filename)
				// Throw exception, Unable to find markdown file
				throw new Kohana_Exception(__("Unable to find :slug Markdown file in :folder", array(':slug' => $this->_slug, ':folder' => $this->_path)));

			// Store filename in _data array
			$this->filename = $this->_filename;
			// Store slug in _data array
			$this->slug = $this->_slug;
			// Parse meta data from markdown file
			$this->_parse_meta();

		}
	}

	/**
	* Retreive and store all files, return valid filename if slug is specified
	**/
	protected function _get_files()
	{
		// Scan modele content directory
		$dir = new DirectoryIterator($this->_path);

		foreach ($dir as $file)
		{
			$filename = $file->getFilename();

			// Skip if is not a valid FlatFile file
			if ( ! $this->_valid_flatfile($filename))
				continue;

			// Extract slug
			$slug = $this->_extract_slug($filename);

			// Match query and filter
			// TODO

			// Store the file
			$this->_files[$slug] = $filename;
		}
	}

	/**
	* Test for valid Flatfile
	**/
	protected function _valid_flatfile($filename)
	{
		$file  = new SplFileInfo($this->_path . $filename);

		if ( ! $file->isFile())
			return false;

		// Match extension
		$pattern = '#^(.*)(\.md|\.markdown)$#';

		if ( ! preg_match($pattern, $filename))
			return false;

		// Test Type mime text/plain ?

		return true;
	}

	/**
	* Extract slug from filename
	*
	* @param	string	Filename
	* @return	string	Slug
	**/
	protected function _extract_slug($filename)
	{
		$pattern = '^([\d]{4}-[\d]{2}-[\d]{2}-|[\d]*-)?'; // Date, increment or nothing
		$pattern = '^([\d]{4}-[\d]{2}-[\d]{2}'.Flatfile::FILE_META_SEPARATOR.'|[\d]*'.Flatfile::FILE_META_SEPARATOR.')?'; // Date, increment or nothing
		$pattern .= '(.*)'; // Slug part
		$pattern .= '(\.md|\.markdown)$'; // File extension;
		preg_match("#$pattern#i", $filename, $matches);
		return $matches[2];
	}

	/**
	* Parse meta data from Markdown file
	**/
	protected function _parse_meta()
	{
		// Open file in read mode
		$file = fopen($this->_path . $this->_filename, 'r');

		// Scan each line
		while ($line = fgets($file))
		{
			// Ending metas zone, load content
			if (strpos($line, Flatfile::METAS_SEPARATOR) !== FALSE)
				break;

			if (($index = strpos($line, ': ')) !== FALSE) // Get new property
			{
				$property = strtolower(substr($line, 0, $index));

				// Preserve filename
				if ($property == 'filename')
					continue;

				// Preserve slug
				if ($property == 'slug')
					continue;

				// Get first value part
				$newline = substr($line, $index + 1);
			}
			else // Store value
			{

				// Adding value
				if (isset($newline))
					$newline .= $line;
			}

			// Store property value
			if (isset($property))
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