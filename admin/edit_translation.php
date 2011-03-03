<?php
/**
 * This source file is is part of Saurus CMS content management software.
 * It is licensed under MPL 1.1 (http://www.opensource.org/licenses/mozilla1.1.php).
 * Copyright (C) 2000-2010 Saurused Ltd (http://www.saurus.info/).
 * Redistribution of this file must retain the above copyright notice.
 * 
 * Please note that the original authors never thought this would turn out
 * such a great piece of software when the work started using Perl in year 2000.
 * Due to organic growth, you may find parts of the software being
 * a bit (well maybe more than a bit) old fashioned and here's where you can help.
 * Good luck and keep your open source minds open!
 * 
 * @package		SaurusCMS
 * @copyright	2000-2010 Saurused Ltd (http://www.saurus.info/)
 * @license		Mozilla Public License 1.1 (http://www.opensource.org/licenses/mozilla1.1.php)
 * 
 */



global $site;

$class_path = '../classes/';
include($class_path.'port.inc.php');
include($class_path.'adminpage.inc.php');

$site = new Site(array(
	'on_debug' => ($_COOKIE['debug'] ? 1 : 0),
	'on_admin_keel' => 1,
));

if (!$site->user->allowed_adminpage(array('script_name' => 'sys_sonad_loetelu.php'))) {
	exit;
}

$sst_id = (int)$site->fdat['sst_id'];

$allowed_edit = true;

if($site->fdat['action'] == 'save' && $site->fdat['op'] == 'edit')
{
	verify_form_token();
	
	foreach($site->fdat['translation'] as $word_id => $translation)
	{
		if(strpos($word_id, 'missing_') === 0)
		{
			$glossary_id = str_replace('missing_', '', $word_id);
			
			$sql = $site->db->prepare("INSERT INTO sys_sonad (sys_sona, keel, sona, origin_sona, sst_id) values(?,?,?,?,?)", $site->fdat['sys_word'], $glossary_id, $translation, $site->fdat['translation_in_cms'][$word_id], $sst_id); 
			$sth_i = new SQL($sql);
		}
		else 
		{
			$sql = $site->db->prepare('update sys_sonad set sona = ? where id = ?', $translation, $word_id);
			new SQL($sql);
		}
	}
	if($site->fdat['type'] == 'popup')
	{
		?>
		<script type="text/javascript">
			window.opener.location = window.opener.location;
			window.close();
		</script>
		<?php
	}
	else 
	{
		header('Location: '.$_SESSION['scms_return_to']);
	}
	
	exit;
}

$error = '';

if($site->fdat['action'] == 'save' && $site->fdat['op'] == 'new' && $sst_id && $site->fdat['sys_word'])
{
	if($allowed_edit)
	{ 
		$at_least_one_translation = false;
		foreach($site->fdat['translation'] as $translation)
		{
			if($translation) 
			{
				$at_least_one_translation = true;
				break;
			}
		}
		
		if($at_least_one_translation)
		{
			$sql = $site->db->prepare('select sys_sona from sys_sonad where sys_sona = ? and sst_id = ? limit 1', $site->fdat['sys_word'], $sst_id);
			$result = new SQL($sql);
			if($result->rows)
			{
				$error = $site->sys_sona(array('sona' => 'glossary_translation_exists', 'tyyp' => 'admin'));
			}
			else 
			{
				$sql = "select distinct keel_id, nimi from sys_sonad left join keel on keel = keel_id";
				$sth = new SQL($sql);
			
				################
				# tsükkel üle kõigi keelte
				while ($keel = $sth->fetch()) {
					# kontrolli, kas süssõna leidub
					$sql = $site->db->prepare("SELECT count(*) FROM sys_sonad WHERE sys_sona = ? and keel=? and sst_id=?", $site->fdat['sys_word'], $keel[keel_id], $sst_id); 
					$sth_s = new SQL($sql);
					$exists = $sth_s->fetchsingle();
			
					# kui ei leidu:
					if (!$exists) {	
			
						# lisa ainult siis kui süssõna pole tühi
						# sys_sonad
						$sql = $site->db->prepare("INSERT INTO sys_sonad (sys_sona, keel, sona, origin_sona, sst_id) values(?,?,?,?,?)", $site->fdat['sys_word'], $keel[keel_id], $site->fdat['translation'][$keel['keel_id']], $site->fdat['translation_in_cms'][$keel['keel_id']], $sst_id); 
						$sth_i = new SQL($sql);
					} 
					
				}
				# / tsükkel üle kõigi keelte
				################
				
				# sys_sonad_kirjeldus
				$sql = $site->db->prepare("INSERT INTO sys_sonad_kirjeldus (sys_sona, sona, sst_id, last_update) values(?,?,?,now())", $site->fdat['sys_word'], ($site->fdat['translation'][1] ? $site->fdat['translation'][1] : array_pop($site->fdat['translation'])), $sst_id); 
				$sth_i = new SQL($sql);
				
				if($site->fdat['type'] == 'popup')
				{
					?>
					<script type="text/javascript">
						window.opener.location = window.opener.location;
						window.close();
					</script>
					<?php
				}
				else 
				{
					header('Location: '.$_SESSION['scms_return_to']);
				}
				exit;
			}
		}
		else
		{
			$error = $site->sys_sona(array('sona' => 'at_least_one_translation_required', 'tyyp' => 'admin'));
		}
	}
}

if($site->fdat['op'] == 'new')
{
	# Sys sõnade tüübid

	$sql = $site->db->prepare("SELECT sys_sona_tyyp.sst_id, sys_sona_tyyp.nimi 
		FROM sys_sona_tyyp
		ORDER BY sys_sona_tyyp.nimi"
	);
	$sth = new SQL($sql);
	$site->debug->msg($sth->debug->get_msgs());
	
	$glossary_word_types = array();
	
	while ($sst = $sth->fetch('ASSOC'))
	{
		$glossary_word_types[] = $sst;
	}
}

// active glossaries
$glossaries = array();

$sql = "select distinct keel.keel_id, keel.nimi, keel.on_default_admin, keel.encoding, keel.locale from keel left join sys_sonad on keel.keel_id = sys_sonad.keel where sys_sonad.keel is not null and keel.keel_id < 500 order by keel.nimi";
$result = new SQL($sql);
while($row = $result->fetch('ASSOC'))
{
	$glossaries[$row['keel_id']] = $row;
}

if($site->fdat['op'] == 'edit')
{
	$word_id = (int)$site->fdat['word_id'];
	
	$sql = $site->db->prepare('select sys_sonad.sys_sona, sys_sonad.sst_id, sys_sona_tyyp.nimi from sys_sonad left join sys_sona_tyyp on sys_sonad.sst_id = sys_sona_tyyp.sst_id where sys_sonad.id = ?', $word_id);
	$result = new SQL($sql);
	if($result->rows)
	{
		$sys_word = $result->fetch('ASSOC');
		$sst_id = $sys_word['sst_id'];
		$sst_name = $sys_word['nimi'];
		$sys_word = $sys_word['sys_sona'];
	}
	else 
	{
		exit;
	}
	
	$sql = $site->db->prepare(
		"SELECT id, sys_sonad.sst_id, nimi, sys_sonad.sona, sys_sonad.sys_sona AS true_sys_sona, sys_sonad.origin_sona, sys_sonad_kirjeldus.sona as sys_sona, sys_sonad.keel 
	     FROM sys_sonad 
	     LEFT JOIN sys_sona_tyyp ON sys_sonad.sst_id = sys_sona_tyyp.sst_id 
	     LEFT JOIN sys_sonad_kirjeldus ON sys_sonad.sst_id=sys_sonad_kirjeldus.sst_id and sys_sonad.sys_sona=sys_sonad_kirjeldus.sys_sona
	     WHERE sys_sonad.sys_sona = ? and sys_sonad.sst_id = ?", $sys_word, $sst_id);
		
	$result = new SQL($sql);
	
	$translations = array();
	
	while($row = $result->fetch('ASSOC'))
	{
		$translations[$row['keel']] = $row;
	}
	
	//printr($translations);
}

	

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<head>

<title><?php echo $site->sys_sona(array('sona' => 'translations', 'tyyp' => 'admin')); ?></title>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $site->encoding; ?>" />

<link rel="stylesheet" href="<?=$site->CONF['wwwroot'].$site->CONF['styles_path']?>/glossary.css" />

<!--[if IE 6]>
	<style type="text/css">
		input.button, input.cancel, input.disabled_button {
			padding: 1px 4px 0px 4px;
		}
	</style>
<![endif]-->

<!--[if IE 7]>
	<style type="text/css">
		input.button, input.cancel, input.disabled_button {
			padding: 1px 8px 0px 8px;
			min-width: 0;
			overflow: visible;
		}
		
		input#create_site_button, input#save_site_button, input#cancel_site_button, input#new_site_cancel_button, input#new_site_create_button {
			width: 85px;
		}
	</style>
<![endif]-->


<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/yld.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/admin_menu.js"></script>
<script type="text/javascript" src="<?=$site->CONF['wwwroot'].$site->CONF['js_path']?>/jquery.js"></script>

<script type="text/javascript">

	var site_url = '<?php echo (empty($_SERVER['HTTPS']) ? 'http://': 'https://').$site->CONF['hostname'].$site->CONF['wwwroot'];?>';
	
</script>

<script type="text/javascript">
$(document).ready(function()
{
	$('input#save_translation_button').click(function ()
	{
		$('form#translations_form').submit();
	});

	$('input#create_translation_button').click(function ()
	{
		if(!$('input#sys_word').attr('value'))
		{
			messageDialog('<?php echo $site->sys_sona(array('sona' => 'translation_word_is_required', 'tyyp' => 'editor')); ?>');
			return
		}
		else
		{
			$('form#translations_form').submit();
		}
	});

	$('input#cancel_button').click(function ()
	{
		<?php if($site->fdat['type'] == 'popup') { ?>
		window.close();
		<?php } else { ?>
		window.document.location.replace('<?php echo $_SESSION['scms_return_to'] ?>');
		<?php } ?>
	});
	
	setContentDimensions();
	
	<?php if($error) { ?>
	messageDialog('<?php echo $error; ?>');
	<?php } ?>
	
	$(window).resize(function()
	{
		setContentDimensions();
	});
});

function setContentDimensions()
{
	// set content height
	$('div#scms_content_cover').height($(window).height());
	
	$('div#scms_content_body').height($(window).height() - $('div#scms_header_bar').height() - (8 + 2 + 2)); // paddings and borders need to be added
}

function messageDialog(message)
{
   	$('div#scms_content_cover').removeClass('hidden');
	
	$('div#scms_dialog, input#message_ok_button').removeClass('hidden');
	
	$('td#message_cell').text(message);
	
	$('input#message_ok_button').click(function ()
	{
		$('div#scms_dialog, input#message_ok_button').addClass('hidden');
		
		$('input#message_ok_button').unbind('click');
		
	   	$('div#scms_content_cover').addClass('hidden');
	});
}

function confirmDialog(question, ok_handler)
{
	$('div#scms_content_cover').removeClass('hidden');
	
	$('div#scms_dialog, input#message_ok_button, input#message_cancel_button').removeClass('hidden');
	
	$('td#message_cell').text(question);
	
	$('input#message_ok_button').click(function ()
	{
		hideConfirmDialog();
		ok_handler();
	});
	
	$('input#message_cancel_button').click(hideConfirmDialog);
}

function hideConfirmDialog()
{
	$('div#scms_dialog, input#message_ok_button, input#message_cancel_button').addClass('hidden');
	
	$('input#message_ok_button, input#message_cancel_button').unbind('click');
	
   	$('div#scms_content_cover').addClass('hidden');
}


</script>

</head>

<body>

	<div id="scms_content_cover" class="hidden"></div>
	
	<div id="scms_dialog" class="hidden">
		<table cellpadding="0" cellspacing="0" id="scms_dialog_box">
			<tr>
				<td id="message_cell"></td>
			</tr>
			<tr>
				<td id="buttons_cell">
					<input id="message_ok_button" type="button" value="<?php echo $site->sys_sona(array('sona' => 'ok', 'tyyp' => 'admin')); ?>" class="button hidden" />
					<input id="message_cancel_button" type="button" value="<?php echo $site->sys_sona(array('sona' => 'cancel', 'tyyp' => 'admin')); ?>" class="cancel hidden" />
				</td>
			</tr>
		</table>
	</div><!-- / scms_dialog -->
	
	<?php if($site->fdat['type'] != 'popup') { ?>
	<div id="scms_header_bar">
		
		<div id="edit_translation_buttons">
			<?php if($site->fdat['op'] == 'edit') { ?><input type="button" id="save_translation_button" value="<?php echo $site->sys_sona(array('sona' => 'save', 'tyyp' => 'admin')); ?>" class="button" /><?php } ?>
			<?php if($site->fdat['op'] == 'new') { ?><input type="button" id="create_translation_button" value="<?php echo $site->sys_sona(array('sona' => 'save', 'tyyp' => 'admin')); ?>" class="button" /><?php } ?>
			<input type="button" id="cancel_button" value="<?php echo $site->sys_sona(array('sona' => 'cancel', 'tyyp' => 'admin')); ?>" class="cancel" />
		</div>
		
	</div><!-- / scms_header_bar -->
	<?php } ?>
	
	<div id="scms_content_body">
	
		<div id="edit_translastion_container">
		
			<form id="translations_form" action="<?=$_SERVER['PHP_SELF']?>" method="POST">
			
				<?php create_form_token('edit-translations'); ?>
				
				<input type="hidden" name="op" value="<?php echo ($site->fdat['op'] == 'edit' ? 'edit' : 'new') ?>" />
				<input type="hidden" name="action" value="save" />
				<input type="hidden" name="action" value="save" />
				<?php if($site->fdat['type'] == 'popup') { ?><input type="hidden" name="type" value="popup" /><?php } ?>
				
				<?php if($site->fdat['op'] == 'edit') { ?><input type="hidden" name="sst_id" value="<?php echo $sst_id; ?>" /><?php } ?>
			
				<table cellpadding="0" cellspacing="0" id="translation_word_table" class="data_table">
					
					<tbody>
						<tr>
							<td class="label"><?php echo $site->sys_sona(array('sona' => 'Systeemi sona', 'tyyp' => 'editor')); ?>:</td>
							<td><?php if($site->fdat['op'] == 'new') { ?><input type="text" name="sys_word" id="sys_word" value="<?php echo htmlspecialchars($site->fdat['sys_word']); ?>" class="long_text" /><?php } else { ?><input type="hidden" name="sys_word" id="sys_word" value="<?php echo $sys_word; ?>" /><?php echo $sys_word; ?><?php } ?></td>
						</tr>
						<tr>
							<td class="label"><?php echo $site->sys_sona(array('sona' => 'type', 'tyyp' => 'admin')); ?>:</td>
							<td><?php if($site->fdat['op'] == 'new') { ?>
								<select name="sst_id" class="select">
									<?php foreach ($glossary_word_types as $word_type) { ?>
									<option value="<?php echo $word_type['sst_id'] ?>"<?php echo ($site->fdat['sst_id'] == $word_type['sst_id'] ? ' selected="selected"' : ''); ?>><?php echo $word_type['nimi']; ?></option>
									<?php } ?>
								</select>
							<?php } else { ?><?php echo $sst_name; ?><?php } ?></td>
						</tr>
					</tbody>
					
				</table>
				
				<table cellpadding="0" cellspacing="0" class="data_table">
					
					<thead>
						<tr>
							<td><?php echo $site->sys_sona(array('sona' => 'Translations', 'tyyp' => 'admin')); ?></td>
							<td><?php echo $site->sys_sona(array('sona' => 'Tolkimine', 'tyyp' => 'admin')); ?></td>
						</tr>
					</thead>
					
					<tbody>
					
						<?php foreach($glossaries as $glossary) { ?>
						<tr>
							<td><?php echo $glossary['nimi']; ?></td>
							<td><input type="text" name="translation[<?php echo ($site->fdat['op'] == 'new' ? $glossary['keel_id'] : ($translations[$glossary['keel_id']]['id'] ? $translations[$glossary['keel_id']]['id'] : 'missing_'.$glossary['keel_id'])); ?>]" value="<?php echo ($site->fdat['op'] == 'new' ? htmlspecialchars($site->fdat['translation'][$glossary['keel_id']]) : htmlspecialchars($translations[$glossary['keel_id']]['sona'])); ?>" class="long_text" /></td>
						</tr>
						<?php } ?>
						
					</tbody>
					
				</table>
				
			</form>
			
		</div><!-- / edit_translastion_container -->
		
	</div><!-- / scms_content_body -->
	
	<?php if($site->fdat['type'] == 'popup') { ?>
	<div id="scms_header_bar">
		
		<div id="edit_translation_buttons" style="float: right;">
			<?php if($site->fdat['op'] == 'edit') { ?><input type="button" id="save_translation_button" value="<?php echo $site->sys_sona(array('sona' => 'save', 'tyyp' => 'admin')); ?>" class="button" /><?php } ?>
			<?php if($site->fdat['op'] == 'new') { ?><input type="button" id="create_translation_button" value="<?php echo $site->sys_sona(array('sona' => 'save', 'tyyp' => 'admin')); ?>" class="button" /><?php } ?>
			<input type="button" id="cancel_button" value="<?php echo $site->sys_sona(array('sona' => 'cancel', 'tyyp' => 'admin')); ?>" class="cancel" />
		</div>
		
	</div><!-- / scms_header_bar -->
	<?php } ?>
	
</body>

</html>