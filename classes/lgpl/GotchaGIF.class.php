<?php

/**
 * Generates GIF image
 *  
 * @package Gotcha
 */
class GotchaGIF extends GotchaImage
{
	/**
	 * image type
	 *
	 * @var		string
	 * @access	public
	 */
	var $type = 'gif';
	
	/**
	 * Constructor function. Sets object variables
	 *
	 * @param	integer	$width
	 * @param	integer	$height
	 * @access	public
	 */
	function GotchaGIF($width, $height)
	{
		$this->GotchaImage($width, $height);
	}
	
	/**
	 * Displays the image
	 *
	 * @param	integer	$quality	image render quality 1-100, default 100
	 * @access	public
	 * @return	void
	 */
	function render($quality = 100)
	{
		header('Content-type: image/'.$this->type);
		@imageinterlace($this->handle, 1);
		@imagegif($this->handle, NULL, $quality);
		@imagedestroy($this->handle);
	}
}

?>