<?php

/**
 * Image effect. Renders text on the image.
 * in OOP terms it should implement Effect interface which looks something likes this
 * interface Effect
 * {
 * 		function apply() {}
 * }
 *  
 * @package Gotcha
 */
class GotchaTextEffect //extends Effect
{
	/**
	 * Text to be rendered on the image
	 *
	 * @var		string
	 * @access	private
	 */
	var $text;
	/**
	 * font size of the text
	 *
	 * @var		integer
	 * @access	private
	 */
	var $size;
	/**
	 * the depth of the shadow behind the text
	 *
	 * @var		integer
	 * @access	private
	 */
	var $depth;
	/**
	 * fonts to use to render the text
	 *
	 * @var		array
	 * @access	private
	 */
	var $fonts = array();
	
	/**
	 * Constructor function. Sets object variables and adds the font list.
	 *
	 * @param	array	$args
	 * @access	public
	 */
	function GotchaTextEffect($args = array())
	{
		$this->text = $args['text'];
		$this->size = $args['size'];
		$this->depth = $args['depth'];
		if(isset($args['fonts']) && is_array($args['fonts'])) foreach($args['fonts'] as $font)
		{
			if(file_exists($font))
			{
				$this->fonts[] = realpath($font);
			}
		}
	}
	
	/**
	 * Apply the effect to the image
	 *
	 * @param	object	$image
	 * @access	public
	 */
	function apply(&$image)
	{
		ini_set('display_errors', 1); // hide all errors from screen
		$c = -1;
		$width = $image->width;
		$height = $image->height;
		$text = strtoupper($this->text);
		$charCount = count($this->fonts);
		if($charCount > 0)
		{
			for($i = 0, $strlen = strlen($this->text), $p = floor(abs((($width-($this->size*$strlen))/2)-floor($this->size/2))); $i < $strlen; $i++, $p +=$this->size)
			{
				$f = $this->fonts[rand(0, $charCount-1)];
				$d = rand(-8, 8);
				$y = rand(floor($height/2)+floor($this->size/2), $height-floor($this->size/2));
				for($b = 0; $b <= $this->depth; $b++)
				{
					imagettftext($image->handle, $this->size, $d, $p++, $y++, $c, $f, $this->text{$i});
				}
				imagettftext($image->handle, $this->size, $d, $p, $y, null, $f, $this->text{$i});
			}
		}
		else
		{
			imagestring($image->handle, $this->size, floor(abs(((($width/2)-($this->size*strlen($this->text)))/2))), floor(($height/2)-($this->size/2)), $this->text, $c );
		}
	}
}

?>