<?php
/**
 * User settings for ElggPG
 */

$user = elgg_get_logged_in_user_entity();

elgg_load_library('elggpg');

if (!elggpg_haskey($user)) {
	echo '<div>' . elgg_echo('elggpg:nopublickey') . '</div>';
	return true;
}

// set default values
if (!$vars['entity']->getUserSetting('encrypt_emails', $user->guid)) {
	$vars['entity']->setUserSetting('encrypt_emails', 'yes', $user->guid);
}
if (!$vars['entity']->getUserSetting('encrypt_site_messages', $user->guid)) {
	$vars['entity']->setUserSetting('encrypt_site_messages', 'no', $user->guid);
}

echo '<div>';
echo elgg_echo('elggpg:encrypt:emails');
echo ' ';
echo elgg_view('input/dropdown', array(
	'name' => 'params[encrypt_emails]',
	'options_values' => array(
		'no' => elgg_echo('option:no'),
		'yes' => elgg_echo('option:yes')
	),
	'value' => $vars['entity']->getUserSetting('encrypt_emails', $user->guid),
));
echo '</div>';

echo '<div>';
echo elgg_echo('elggpg:encrypt:site_messages');
echo ' ';
echo elgg_view('input/dropdown', array(
	'name' => 'params[encrypt_site_messages]',
	'options_values' => array(
		'no' => elgg_echo('option:no'),
		'yes' => elgg_echo('option:yes')
	),
	'value' => $vars['entity']->getUserSetting('encrypt_site_messages', $user->guid),
));
echo '</div>';
