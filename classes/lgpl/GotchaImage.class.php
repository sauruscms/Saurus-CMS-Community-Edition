<?php

/**
 * Image generation baseclass
 *  
 * @package Gotcha
 */
class GotchaImage
{
	/**
	 * image width
	 *
	 * @var		integer
	 * @access	public
	 */
	var $width;

	/**
	 * image height
	 *
	 * @var		integer
	 * @access	public
	 */
	var $height;

	/**
	 * image handle
	 *
	 * @var		resource
	 * @access	public
	 */
	var $handle;
	
	/**
	 * Constructor function. Sets object variables
	 *
	 * @param	integer	$width
	 * @param	integer	$height
	 * @access public
	 */
	function GotchaImage($width, $height)
	{
		$this->width = $width;
		$this->height = $height;
	}
	
	/**
	 * Applies an effect to the image
	 *
	 * @access public
	 * @return void
	 */
	function apply(&$effect)
	{
		$effect->apply($this);
	}
	
	/**
	 * Creates random colored image, retruns the image handle
	 *
	 * @access public
	 * @return resource
	 */
	function create()
	{
		$this->handle = @imagecreate($this->width, $this->height);
		@imagecolorallocate($this->handle, rand(0, 255), 255, rand(0, 255));
		return $this->handle;
	}
	
	/**
	 * abstract function, for image displaying, must be implemented by extenders
	 */
	function render() {}
}

?>