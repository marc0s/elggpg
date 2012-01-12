<?php
/**
 * ElggPG Sidebar
 * 
 * @package ElggPG
 */

$owner = elgg_get_page_owner_entity();

if ($owner->guid != elgg_get_logged_in_user_guid() || !$owner->openpgp_publickey) {
	return true;
}

$enabled = elgg_get_plugin_user_setting('encrypt_emails', $owner->guid, 'elggpg') != 'no';
$enabled = $enabled ? 'enabled' : 'disabled';

$body = '<p>' . elgg_echo("elggpg:notifications:$enabled") . '</p>';
$body .= elgg_view('output/url', array(
	'text' => elgg_echo('elggpg:notifications:settings'),
	'href' => "http://18.lorea/settings/plugins/$owner->username",
	'is_trust' => true,
));

echo elgg_view_module('aside', elgg_echo('elggpg:notifications'), $body);
