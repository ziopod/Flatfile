<?php
/**
* HTTP Exception
*
* **Evaluate before adding to flatfile module core**
*
* @package		Flatfile
* @category		Evaluate
**/
class HTTP_Exception extends Kohana_HTTP_Exception {

	public function get_response()
	{
	// 	if ($this->_code === 503)
	// 	{
	// 		$layout = Kostache_Layout::factory('layout/empty');
	// 	}
	// 	else
	// 	{
			// $layout = Kostache_Layout::factory();
	// 	}

		$layout = Kostache_Layout::factory();

		$view = new View_Error;
		$view->set_error_code($this->_code);
		$view->message = $this->getMessage();
		$render = $layout->render($view);

		$response = Response::factory()
			->status($this->_code)
			->body($render);

		return $response;
	}	
}