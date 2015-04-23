<?php defined('SYSPATH') OR die ('No direct script access');
/**
* # Flatfile
*
* Model for Flatfile 
*
* @package		Flatfile
* @author		Ziopod <ziopod@gmail.com>
* @copyright	(c) 2013-2014 Ziopod
* @license		http://opensource.org/licenses/MIT
**/

use Michelf\MarkdownExtra;

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
	* @var array	List of slug/file name
	* Evaluate : List of model content files (match by filters and queries)
	**/
	protected $_files = array();

	/**
	* @var string	Sort order files
	**/
	protected $_order = 'desc';

	/**
	* @var array	Query terms
	**/
	protected $_query;// = array();

	/**
	* @var string	Query offset
	**/
	protected $_offset;

	/**
	* @var string	Query limit
	**/
	protected $_limit = 20; 

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
		$sub_folders = explode('/', trim($slug));
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
		if ($slug != NULL)
		{
			$this->_slug = $slug; // Store slug
			// A slug spécified, automaticly find file
			$this->find();
		}

	}

	// Return headline
	public function headline()
	{
		return trim($this->headline);
	}

	// Return content
	public function content()
	{
		return trim($this->content);
	}

	/**
	* Define filter
	* **Inspire by Kohana ORM::run_filter()**
	**/
	public function filters()
	{
		return array();
	}

	/**
	* Markdown filter
	*
	* @param	string	Markdown
	* @return 	string	HTML
	**/
	public static function Markdown($str)
	{
		return trim(MarkdownExtra::defaultTransform($str));
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
	* Ordering files
	**/
	public function order($order)
	{
		if ($order === 'asc')
		{
			$this->_order = 'asc';		
		}

		return $this;
	}

	/**
	* Adding terms to quey queue
	**/
	public function query($property, $op = NULL, $term = NULL)
	{
		$this->_query[] = array($property, $op, $term);
		return $this;
	}

	/**
	* Query offset for find all method
	**/
	public function offset($offset)
	{
		$this->_offset = $offset;
		return $this;
	}

	/**
	* Query limit for find all method
	**/
	public function limit($limit)
	{
		$this->_limit = $limit;
		return $this;
	}
	/**
	* Find file an load data
	**/
	public function find()
	{
		// Constant STATE_LOADING
		// Try to load file data
		//return 
		return $this->_load();
	}

	/**
	* Find all data from folder and return references list
	**/
	public function find_all()
	{
		// Try to grab multiple md files
		return $this->_load(TRUE);
		// return $this;
	}
	
	/**
	*  Quering
	**/
	public function _quering($queries)
	{

		foreach($queries as $query)
		{
			// Get property
			$property = $query[0];

			if ($property === 'date')
				return TRUE;

			// Operator
			$operator = $query[1];
			
			// Property are set in data ?
			if ( ! isset($this->_data[$property]))
				continue;

			// Search in value?
			if ( ! empty($operator))
			// if ( ! empty($query[2]) OR (isset($query[1]) AND ! isset($query[2])))
			{
				$term = $query[2];
				$value = $this->_data[$property];

				// Integer
				if (is_int($term))
					if ( ! is_numeric($value))
						continue;

				// Boolean
				if (is_bool($term))
					if (strtoupper($value) !== 'TRUE' AND strtoupper($value) !== 'FALSE')
						continue;

				// String
				$term = (string) $term;

				if ($operator === '=' AND $term != $value)
					continue;

				if ($operator === '!=' AND $term == $value)
					continue;
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	* Load data
	**/
	protected function _load($multiple = FALSE)
	{
		// Get files
		$this->_get_files();

		// Match on property, terms and other stuffs
		// TODO

		// if ($multiple === TRUE)
		if ($multiple === TRUE OR $this->_query)
		{
			// Loading multiple Flatfile
			$result = array();
			// Natural sort ordering
			natsort($this->_files);

			// Ordering files
			if ($this->_order === 'desc')
			{
				$this->_files = array_reverse($this->_files, TRUE);	
			}
	
			// Each md file is load in array and returned
			foreach ($this->_files as $slug => $file)
			{

				// Instantiate current Flatfile
				$flatfile = Flatfile::factory($this->_type, $slug);

				// Match query
				// If not multiple 
				if ($this->_query)
				{
					// If Flatfile not matching with queries, ignore it
					if ( ! $flatfile->_quering($this->_query))
					{
						continue;
					}

					// If we want only one result
					if ($multiple === FALSE)
					{
						// Return first result
						return $flatfile;
					}
				}

				if ($this->_offset)
				{
					$this->_offset --;

					if ($this->_offset !== -1)
					{
						continue 1;
					}
				}

				// Add Flatfile object to list of result
				$result[] = $flatfile;

				if ($this->_limit AND ! $this->_offset)
				{
					$this->_limit --;

					if ($this->_limit === 0)
					{
						break 1;
					}
				}
			}

			return $result;

		}
		else
		{

			// If any slug specified, we load the first post
			if ( ! $this->_slug)
			{
				if ($this->_files)
				{
					$this->_slug = $this->_extract_slug(current($this->_files));
				}
				else
				{
					return NULL;
				}
			}

			if (isset($this->_files[$this->_slug]))
			{
				$this->_filename = $this->_files[$this->_slug];
			}

			if ( ! $this->_filename)
			{
				// Throw exception, Unable to find markdown file
				throw new Kohana_Exception(__("Unable to find :slug Markdown file in :folder", array(':slug' => $this->_slug, ':folder' => $this->_path)));
			}

			// Store filename in _data array
			$this->filename = $this->_filename;
			// Store slug in _data array
			$this->slug = $this->_slug;
			// Store date
			$this->date = $this->_extract_date($this->filename);
			// Parse meta data from markdown file
			$this->_parse_meta();
			return $this;
		}
	}

	/**
	* Retreive and store all files, return valid filename if slug is specified
	**/
	protected function _get_files()
	{
		// Scan modele content directory
		try
		{
			$dir = new DirectoryIterator($this->_path);		
		}
		catch (UnexpectedValueException $a)
		{
			throw new Kohana_Exception(__("Unable to find directory :path", array(':path' => $this->_path)));			
		}

		foreach ($dir as $file)
		{
			$filename = $file->getFilename();

			// Skip if is not a valid FlatFile file
			if ( ! $this->_valid_flatfile($filename))
				continue;

			// Extract slug
			$slug = $this->_extract_slug($filename);

			// Match query on date or slug
			// TODO
			// Quering based filename data (slug and date)
			if ($this->_query)
			{
				foreach($this->_query as $key => $query)
				{
					// Get property
					$property = $query[0];
					$operator = $query[1];
					$value = $query[2];

					// Test only date and slug on filename; ignore others
					if ($property === 'date')
					{
						
						$date = strtotime($this->_extract_date($filename));
						$value = strtotime($value);

						// Sup
						if ($operator === '>' AND ($date <= $value))
							continue 2; // Ignore this file

						// Sup or egale
						if ($operator === '>=' AND ($date < $value))
							continue 2;

						// Inf
						if ($operator === '<' AND ($date >= $value))
							continue 2;

						// Inf or egale
						if ($operator === '<=' AND ($date > $value))
							continue 2;
					}					
					
					
				}

			}



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
	* Extraxt date form filename
	*
	* @param	string	Filename
	* @return	string	Timestamp date
	**/
	protected function _extract_date($filename = NULL)
	{
		$filename = $filename ? $filename : $this->filename;
		$pattern = '([\d]{4}-[\d]{2}-[\d]{2})?'; // Date, increment or nothing
		preg_match("#$pattern#i", $filename, $matches);
		return $matches[0];
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

			if (($index = strpos($line, ':')) !== FALSE) // Get new property
			{
				$property = trim(strtolower(substr($line, 0, $index)));

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

		if (!is_file($file))
			return NULL;
		
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

		$m = new Mustache_Engine;
		$this->headline = $m->render($headline, $this);
		$this->content = $m->render($content, $this); // Trim ?

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

		if ($key ==='date')
			return $this->_extract_date();

		if (array_key_exists($key, $this->_data))
			return $this->_run_filter($key, $this->_data[$key]);

		return NULL;
		// throw new Flatfile_Exception('Property ' . $key . ' does not exist in ' . get_class($this) . ' !');
	}

	/**
	* Magic isset to test _data
	**/
	public function __isset($key)
	{
		// STATE_LOADED
		// if ($this->_loaded !== TRUE)
		// 	$this->find();

		return isset($this->_data[$key]);
	}

}