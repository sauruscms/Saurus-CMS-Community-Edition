<?php

/**
 * Image effect. Fills image with gradient.
 * in OOP terms it should implement Effect interface which looks something likes this
 * interface Effect
 * {
 * 		function apply() {}
 * }
 *  
 * @package Gotcha
 */
class GotchaGradientEffect //extends Effect
{
	/**
	 * Constructor function. Sets object variables
	 *
	 * @param	array	$args
	 * @access	public
	 */
	function GotchaGradientEffect($args = array())
	{
		
	}
	
	/**
	 * Apply the effect to the image
	 *
	 * @param	object	$image
	 * @access	public
	 */
	function apply(&$image)
	{
		for($i = 0, $rd = rand(0, 100), $gr = rand(0, 100), $bl= rand(0, 100); $i <= $image->height; $i++)
		{
			$g = @imagecolorallocate($image->handle, $rd+=2, $gr+=2, $bl+=2);
			@imageline($image->handle, 0, $i, $image->width, $i, $g);
		}
	}
}

?>