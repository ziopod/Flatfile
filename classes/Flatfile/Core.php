<?php

/**
* Core model for Flatfile
*
* @package      Flatfile
* @author       Ziopod <ziopod@gmail.com>
* @copyright    (c) 2013-2014 Ziopod
* @license      http://opensource.org/licenses/MIT
**/

class Flatfile_Core
{
    
    /**
    * @var  string  Type of Flatfile Model
    **/
    protected $_type;

    /**
    * @var string   Content folder witch contain files
    **/
    protected $_folder;

    /**
    * @var  string  Path to the folder which contain files
    */
    protected $_path;

    /**
    * @var  string  Markdown filename
    **/
    protected $_filename;

    /**
    * @var  string  Slug
    **/
    protected $_slug;

    /**
    * @var array    List of slug/file name
    * Evaluate : List of model content files (match by filters and queries)
    **/
    protected $_files = array();

    /**
    * @var string   Sort order files
    **/
    protected $_order = 'desc';

    /**
    * @var array    Query terms
    **/
    protected $_query;// = array();

    /**
    * @var string   Query offset
    **/
    protected $_offset;

    /**
    * @var string   Query limit
    **/
    protected $_limit = 20;

    /**
    * @var  array   For storing file data
    **/
    protected $_data = array();

    /**
    * @var  string  State of flafile
    **/
    protected $_state;

    const CONTENTDIR = 'content';
    const FILE_META_SEPARATOR = '_';
    const METAS_SEPARATOR = '---';
    const STATE_LOADED = 10;

    /**
    * Return a model of the model na provided. You can spacify an file
    * slug (file name base) for load or create o specifque file model
    *
    *       $post = self::factory('Post', 'my-lovely-post');
    *
    * @chainable
    * @param    string  The model name
    * @param    string  A specific slug
    *
    * @return   object
    */
    public static function factory($model, $slug = null)
    {
        $model = 'Model_' . ucfirst($model);
        return new $model($slug);
    }

    /**
    * Construct empty model or try to fetch specific file when slug are provided
    *
    *       $post = new Post_Model('my-lovely-post');
    *
    * @param    string  A specific slug
    */
    public function __construct($slug = null)
    {
        // Get subfolders
        $sub_folders = explode('/', trim($slug));
        // Get slug part
        $slug = array_pop($sub_folders);

        // Store subfolders
        if (! empty($sub_folders)) {
            $sub_folders = implode(DIRECTORY_SEPARATOR, $sub_folders);
        }

        // Store type
        $folder = null;
        $classname = get_class($this);

        if ($classname !== 'Flatfile') {
            $this->_type = strtolower(substr($classname, 6)); // Remove model_ to the classe name
            $folder = str_replace('_', '/', $this->_type) . DIRECTORY_SEPARATOR;
            //$folder = Inflector::plural(str_replace('_', '/', $this->_type)) . DIRECTORY_SEPARATOR;
        }

        // Store folder path
        $this->_folder = self::CONTENTDIR . DIRECTORY_SEPARATOR . $folder;

        if ($sub_folders) {
            $this->_folder .= $sub_folders . DIRECTORY_SEPARATOR;
        }

        // $this->_path = DOCROOT . $this->_folder;
        $this->_path = $this->_folder;

        // Trying to load Flatile if slug is provided
        if ($slug != null) {
            $this->_slug = $slug; // Store slug
            // A slug spécified, automaticly find file
            $this->find();
        }
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
    * Ordering files
    **/
    public function order($order)
    {
        if ($order === 'asc') {
            $this->_order = 'asc';
        }

        return $this;
    }

    /**
    * Adding terms to quey queue
    **/
    public function query($property, $op = null, $term = null)
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
        return $this->_load(true);
        // return $this;
    }
    
    /**
    * Return loaded state
    **/
    public function loaded()
    {
        return $this->_state === self::STATE_LOADED;
    }

    /**
    * Return content part
    **/
    public function content()
    {
        return $this->content;
    }

    /**
    *  Quering
    **/
    public function _quering($queries)
    {

        foreach ($queries as $query) {
            // Get property
            $property = $query[0];

            if ($property === 'date') {
                return true;
            }

            // Operator
            $operator = $query[1];
            $term = $query[2];
            
            // Term are not boolean or NULL and property are set in data
            if (! (is_bool($term) or $term === null) and ! isset($this->_data[$property])) {
                continue;
            }

            // Search in value?
            if (! empty($operator)) {
                $value = isset($this->_data[$property]) ? trim($this->_data[$property]) : null;

                // Integer
                if (is_int($term)) {
                    if (! is_numeric($value)) {
                        continue;
                    }
                }

                // Boolean
                if (is_bool($term)) {
                    // Attempt to convert to boolean value
                    if ((bool) $value === false or strtoupper($value) === 'FALSE') {
                        $value = false;
                    } else if ($value !== null) {
                        $value = true;
                    }
                }


                // String
                $term = (string) $term;

                if ($operator === '=' and $term != $value) {
                    continue;
                }

                if ($operator === '!=' and $term == $value) {
                    continue;
                }
            }

            return true;
        }

        return false;
    }

    /**
    * Load data
    **/
    protected function _load($multiple = false)
    {
        // Get files
        $this->_get_files();

        // Match on property, terms and other stuffs
        // TODO

        // if ($multiple === TRUE)
        if ($multiple === true or $this->_query) {
            // Loading multiple Flatfile
            $result = array();
            // Natural sort ordering
            natsort($this->_files);

            // Ordering files
            if ($this->_order === 'desc') {
                $this->_files = array_reverse($this->_files, true);
            }
    
            // Each md file is load in array and returned
            foreach ($this->_files as $slug => $file) {
                // Instantiate current Flatfile
                $flatfile = self::factory($this->_type, $slug);

                // Match query
                // If not multiple
                if ($this->_query) {
                    // If Flatfile not matching with queries, ignore it
                    if (! $flatfile->_quering($this->_query)) {
                        continue;
                    }

                    // If we want only one result
                    if ($multiple === false) {
                        // Return first result
                        return $flatfile;
                    }
                }

                if ($this->_offset) {
                    $this->_offset --;

                    if ($this->_offset !== -1) {
                        continue 1;
                    }
                }

                // Add Flatfile object to list of result
                $result[] = $flatfile;

                if ($this->_limit and ! $this->_offset) {
                    $this->_limit --;

                    if ($this->_limit === 0) {
                        break 1;
                    }
                }
            }

            return $result;
        } else {
            // If any slug specified, we load the first post
            if (! $this->_slug) {
                if ($this->_files) {
                    $this->_slug = $this->_extract_slug(current($this->_files));
                } else {
                    return null;
                }
            }

            // Try to find file by slug
            if (isset($this->_files[$this->_slug])) {
                $this->_filename = $this->_files[$this->_slug];
            } else if (array_key_exists($this->_slug, array_flip($this->_files))) {
                // Try by filename
                $this->_filename = $this->_slug;
                $this->_slug = $this->_extract_slug($this->_slug);
            }

            if (! $this->_filename) {
                // No file find, nothing to do.
                return null;
            }

            // Change State
            $this->_state = self::STATE_LOADED;
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
        try {
            $dir = new DirectoryIterator($this->_path);
        } catch (UnexpectedValueException $a) {
            throw new Kohana_Exception(__("Unable to find directory :path", array(':path' => $this->_path)));
        }

        foreach ($dir as $file) {
            $filename = $file->getFilename();

            // Skip if is not a valid FlatFile file
            if (! $this->_valid_flatfile($filename)) {
                continue;
            }

            // Extract slug
            $slug = $this->_extract_slug($filename);

            // Match query on date or slug
            // TODO
            // Quering based filename data (slug and date)
            if ($this->_query) {
                foreach ($this->_query as $key => $query) {
                    // Get property
                    $property = $query[0];
                    $operator = $query[1];
                    $value = $query[2];

                    // Test only date and slug on filename; ignore others
                    if ($property === 'date') {
                        $date = strtotime($this->_extract_date($filename));
                        $value = strtotime($value);

                        // Sup
                        if ($operator === '>' and ($date <= $value)) {
                            continue 2; // Ignore this file
                        }

                        // Sup or egale
                        if ($operator === '>=' and ($date < $value)) {
                            continue 2;
                        }

                        // Inf
                        if ($operator === '<' and ($date >= $value)) {
                            continue 2;
                        }

                        // Inf or egale
                        if ($operator === '<=' and ($date > $value)) {
                            continue 2;
                        }
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

        if (! $file->isFile()) {
            return false;
        }

        // Match extension
        $pattern = '#^(.*)(\.md|\.markdown)$#';

        if (! preg_match($pattern, $filename)) {
            return false;
        }

        // Test Type mime text/plain ?

        return true;
    }

    /**
    * Extract slug from filename
    *
    * @param    string  Filename
    * @return   string  Slug
    **/
    protected function _extract_slug($filename)
    {
        $pattern = '^([\d]{4}-[\d]{2}-[\d]{2}-|[\d]*-)?'; // Date, increment or nothing
        $pattern = '^([\d]{4}-[\d]{2}-[\d]{2}'.self::FILE_META_SEPARATOR.'|[\d]*'.self::FILE_META_SEPARATOR.')?'; // Date, increment or nothing
        $pattern .= '(.*)'; // Slug part
        $pattern .= '(\.md|\.markdown)$'; // File extension;
        preg_match("#$pattern#i", $filename, $matches);
        return $matches[2];
    }

    /**
    * Extraxt date form filename
    *
    * @param    string  Filename
    * @return   string  Timestamp date
    **/
    protected function _extract_date($filename = null)
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
        while ($line = fgets($file)) {
            // Ending metas zone, load content
            if (strpos($line, self::METAS_SEPARATOR) !== false) {
                break;
            }

            /**
            * New property are :
            *
            * - word:
            * - [space][space][space][space]word:
            * - [tab]word:
            **/
            if (preg_match('/^(|\x20{4}|[\t])\w+:/', $line, $matches)) { // Get new property
            // Remove ":"
                $index = strpos($line, ':');
                $property = trim(strtolower(substr($line, 0, $index)));

                // Preserve filename
                if ($property == 'filename') {
                    continue;
                }

                // Preserve slug
                if ($property == 'slug') {
                    continue;
                }

                // Get first value part
                $newline = substr($line, $index + 1);
            } else // Store value
            {
                // Adding value
                if (isset($newline)) {
                    $newline .= $line;
                }
            }

            // Store property value
            if (isset($property)) {
                $this->$property = rtrim($newline);
            }
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
        $skip_metas = true;
        $content = '';

        if (!is_file($file)) {
            return null;
        }
        
        foreach (new SplFileObject($file) as $line) {
            if ($skip_metas) {
                if (strpos($line, self::METAS_SEPARATOR) !== false) {
                    $skip_metas = false;
                }
            } else {
                $content .= $this->_complete_url($line);
            }
        }

        $this->content = $content; // Trim ?
    }

    /**
    * Add base url, locale content directory
    *
    */
    protected function _complete_url($line)
    {

        // Image path
        if (preg_match_all('/!\[([^]]*)\] *\(([^)]*)\)/', $line, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $matche) {
                if (isset($matche[2])) {
                    if (! Valid::url($matche[2])) {
                        $replacement = '![' . $matche[1] . '](' . URL::base(true, false) . $this->_folder . $matche[2] . ')';
                        $line = preg_replace('/' . preg_quote($matche[0], '/') . '/', $replacement, $line);
                    }
                }
            }

            return $line;
        } else if (preg_match_all('/\[([^]]*)\] *\(([^)]*)\)/', $line, $matches, PREG_SET_ORDER)) {
            // Link
            foreach ($matches as $matche) {
                if (isset($matche[2])) {
                    // If URL do not contain a abslolute path
                    if (! Valid::url($matche[2])) {
                        $replacement = '[' . $matche[1] . '](' . URL::base(true, false) . $matche[2] .')';
                        $line = preg_replace('/' . preg_quote($matche[0], '/') . '/', $replacement, $line);
                    }
                }
            }

            return $line;
        }

        return $line;
    }

    /**
    * Filters a value for a specific property
    * **Inspire by Kohana ORM::run_filter()**
    *
    * @param    string  Property name
    * @param    string  Value to filter
    **/
    protected function _run_filter($property, $value)
    {
        $filters = $this->filters();
        // Get filters fot this property
        $wildcards = empty($filters[true]) ? array() : $filters[true];
        // Merge in the wildcards
        $filters = empty($filters[$property]) ? $wildcards : array_merge($wildcards, $filters[$property]);
        // Bind the property an model
        $_bound = array(
            ':property' => $property,
            ':model'    => $this,
        );

        foreach ($filters as $array) {
            $_bound[':value'] = $value;
            $filter = $array[0];
            $params= Arr::get($array, 1, array(':value'));

            foreach ($params as $key => $param) {
                if (is_string($param) and array_key_exists($param, $_bound)) {
                    // Replace with bound value
                    $params[$key] = $_bound[$param];
                }
            }

            if (is_array($filter) or ! is_string($filter)) {
                // Callback as an array or a lambda
                $value = call_user_func_array($filter, $params);
            } elseif (strpos($filter, '::') === false) {
                // Use a function call
                $function = new ReflectionFunction($filter);
                // Call $function($this[$property], $param, …) With Reflection
                $value = $function->invokeArgs($params);
            } else {
                // Split the class and method of the rule
                list($class, $method) = explode('::', $filter, 2);
                // Use static method call
                $method = new ReflectionMethod($class, $method);
                // Class $class::$method($this[$property], $param, …) with Reflection
                $value = $method->invokeArgs(null, $params);
            }
        }

        return $value;
    }

    /**
    * Set value in data array
    *
    * @return   void
    **/
    public function __set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    /**
    * Try to get a value from data array
    *
    * @throws   Flatfile_Exception
    * @param    string  Key of data array
    * @return   mixte
    **/
    public function __get($key)
    {
        // if ($key === 'content' AND $this->_loaded)
        if ($key === 'content') {
            $this->_parse_content();
        }

        // Attempt to extract date from filename if available
        if ($key ==='date') {
            $date = $this->_extract_date();

            if ($date) {
                return $date;
            }
        }

        if (array_key_exists($key, $this->_data)) {
            return $this->_run_filter($key, $this->_data[$key]);
        }

        return null;
        // throw new Flatfile_Exception('Property ' . $key . ' does not exist in ' . get_class($this) . ' !');
    }

    /**
    * Magic isset to test _data
    **/
    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }
}
