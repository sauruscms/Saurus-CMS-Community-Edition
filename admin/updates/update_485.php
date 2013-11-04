<?php

function up_485()
{
	new SQL("INSERT INTO config (nimi, sisu, kirjeldus, on_nahtav) VALUES ('allow_onsite_translation', '1', 'Allow on-site translation (syswords)', '1')");
}
?>