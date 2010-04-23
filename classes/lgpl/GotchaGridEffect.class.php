<?php

/**
 * Image effect. Renders a grid on the image.
 * in OOP terms it should implement Effect interface which looks something likes this
 * interface Effect
 * {
 * 		function apply() {}
 * }
 *  
 * @package Gotcha
 */
class GotchaGridEffect //extends Effect
{
	/**
	 * size of the overlayed grid
	 *
	 * @var		integer
	 * @access	private
	 */
	var $size;
	
	/**
	 * Constructor function. Sets object variables
	 *
	 * @param	array	$args
	 * @access	public
	 */
	function GotchaGridEffect($args = array())
	{
		$this->size = $args['size']; //rand($size, 10);
	}
	
	/**
	 * Apply the effect to the image
	 *
	 * @param	object	$image
	 * @access	public
	 */
	function apply(&$image)
	{
		for($i = 0, $x = 0, $z = $image->width; $i < $image->width; $i++, $z -= $this->size, $x += $this->size){
			@imageline($image->handle, $x, 0, $x+10, $image->height, null);
			@imageline($image->handle, $z, 0, $z-10, $image->height, null);
		} 
	}
}

?>