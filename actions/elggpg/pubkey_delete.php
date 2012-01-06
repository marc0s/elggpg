<?php
/**
 * Elgg GnuPG
 *
 * @package ElggPG
 */

$user = get_user_by_username(get_input('username'));

if (!elgg_is_logged_in() || !$user || !$user->canEdit()) {
	register_error(elgg_echo("elggpg:delete:error"));
	forward(REFERER);
}

elgg_load_library('elggpg');
$info = elggpg_delete_key($user);

if ($info) {
	system_message(elgg_echo("elggpg:deleted"));
} else {
	register_error(elgg_echo("elggpg:delete:error"));		
}

forward(REFERER);
