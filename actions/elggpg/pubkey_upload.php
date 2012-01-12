<?php
/**
 * Elgg GnuPG
 *
 * @package ElggPG
 */

$user = get_user_by_username(get_input('username'));
$public_key = get_uploaded_file('public_key');

if (!elgg_is_logged_in() || !$user || !$user->canEdit() || $public_key === false) {
	register_error(elgg_echo("elggpg:upload:error"));
	forward(REFERER);
}

elgg_load_library('elggpg');
$info = elggpg_import_key($public_key, $user);

if ($info['unchanged']) {
	system_message(elgg_echo("elggpg:upload:unchanged"));			
} elseif ($info['imported']) {
	add_to_river('river/elggpg/update', 'addkey', $user->getGUID(), $user->getGUID());
	
	system_message(elgg_echo("elggpg:upload:imported", array($info['key_id'])));
}

system_message(elggpg_import_report($info));
forward(REFERER);
