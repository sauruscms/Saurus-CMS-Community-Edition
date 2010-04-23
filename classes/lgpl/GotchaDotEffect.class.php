<?php

/**
 * Image effect. Scatters random dots on the image.
 * in OOP terms it should implement Effect interface which looks something likes this
 * interface Effect
 * {
 * 		function apply() {}
 * }
 *  
 * @package Gotcha
 */
class GotchaDotEffect
{
	/**
	 * Constructor function. Sets object variables
	 *
	 * @param	array	$args
	 * @access	public
	 */
	function GotchaDotEffect($args = array())
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
		for($i = 0; $i < $image->width; $i++)
		{
			@imagesetpixel( $image->handle, rand(0, $image->width), rand(0, $image->height), @imagecolorallocate($image->handle, rand(0, 255), rand(0, 255), rand(0, 255)) );
		}
	}
}

?>