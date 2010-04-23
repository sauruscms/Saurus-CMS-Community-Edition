<?php
/**
* Smarty plugin
* @package Smarty
* @subpackage plugins
*/

/**
* Smarty {while} block function plugin
*
* Type:     block function<br>
* Name:     while<br>
* Date:     Mar 23 2006<br>
* Purpose:  provide a while-loop<br>
* Input:    var = string, name of a boolean variable
* Examples: The following code will loop 1 time ;)
*     <pre>
*     {assign var="repeat" value=true}
*     {while var="repeat"}
*       hello world
*       {assign var="repeat" value=false}
*     {/while}
*     </pre>
* @author Misha Aizatulin <avatar@hot.ee>
*/

function smarty_block_while($params,  $content, &$smarty, &$repeat)
{
  if(!isset($params['var']))
    $smarty->trigger_error("while: missing parameter 'var'", E_USER_ERROR);

  // we have to take the name of the variable instead of
  // the variable itself, because parameters to blocks are
  // computed only once
  $repeat = $smarty->get_template_vars($params['var']);
 
  return $content;
}
?>