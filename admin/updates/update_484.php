<?php

function up_484()
{
	new SQL("INSERT INTO `config` (`nimi`, `sisu`, `kirjeldus`, `on_nahtav`) VALUES ('custom_login_url', '', 'Enter a custom login page URL instead of the standard form like:  http://www.example.com', '1')");
	new SQL("INSERT INTO `config` (`nimi`, `sisu`, `kirjeldus`, `on_nahtav`) VALUES ('disable_form_based_login', '0', 'Disable form based login', '1')");
}
?>