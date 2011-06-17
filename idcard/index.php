<?php
/**
 * Redirect user from SSL folder back to the website root folder, to file idcard.php.
 *
 * @package CMS
 */

# Bug #2415: targeturl
if($_GET['targeturl']) { $targeturl = '?target_url='.$_GET['targeturl']; }
elseif($_POST['targeturl']) { $targeturl = '?target_url='.$_POST['targeturl']; }


header('Location: ../idcard.php'.$targeturl);
exit;
