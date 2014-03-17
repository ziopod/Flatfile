<?php

/**
* You can replace this class with your own stuffs
*
* For example, you can aggregate FlatFile :
*
*	public function aside()
*	{
*		$flatfile_slug = 'asides/my_cool_aside';
*
*		try
*		{
*			return new Model_Page($flatfile_slug);
*		}	
*		catch(Kohana_Exception $e)
*		{
*			Log::instance()->add(Log::WARNING, 'Unable to find file ":slug.md", please check your "content" directory', array(
*					':slug' => $flatfile_slug,
*			));
*		}
*	}
*
* @package		Flatfile
* @category		View Model
* @author		Ziopod <ziopod@gmail.com>
* @copyright	(c) 2013-2014 Ziopod
* @license		http://opensource.org/licenses/MIT
**/

class View_Home extends View_Page{}