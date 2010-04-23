<?php

/**
 * file: captcha_image.php
 * Generates CAPTCHA image based on the definition in extension.congig.php.
 * Standalone script.
 * 
 * @package saurus4_captcha
 */

session_start();
	
//erase previous captchas
$_SESSION['scms_captcha'] = array();

global $EXTENSION;

$class_path = '../../classes/';

//load captcha extension config
include_once('extension.config.php');

//name of the captcha definition
$name = (string)$_GET['name'];

foreach($EXTENSION['captchas'] as $cap_name => $cap_def	)
{
	$captcha_def = false;
	if($name == $cap_name) 
	{
		$captcha = $cap_def;
		break;
	}
}

if(!$captcha)
{
	//unknown defintion, exit
	exit;
}

include_once($class_path.'lgpl/GotchaImage.class.php');

//what image format to use?
switch ($captcha['image_type'])
{
	case 'gif':
		include_once($class_path.'lgpl/GotchaGIF.class.php');
		$img = new GotchaGIF($captcha['image_width'], $captcha['image_height']);
		break;
	case 'jpg':
		include_once($class_path.'lgpl/GotchaJPG.class.php');
		$img = new GotchaJPG($captcha['image_width'], $captcha['image_height']);
		break;
	case 'png':
		include_once($class_path.'lgpl/GotchaPNG.class.php');
		$img = new GotchaPNG($captcha['image_width'], $captcha['image_height']);
		break;
	default:
		//unknown, exit 
		exit;
		break;
}

//create image
if($img->create())
{
	//apply effects
	foreach($captcha['effects'] as $effect)
	{
		$effect_name = $effect['name'];
		//echo $effect_name;
		include_once($class_path.'lgpl/'.$effect_name.'.class.php');
		$img->apply(new $effect_name($effect['args']));
	}
	
	//write the text into session
	$_SESSION['scms_captcha'][$name] = $EXTENSION['captchas'][$name]['text_to_verify'];
	
	//display image;
	$img->render();
}

exit;

?>