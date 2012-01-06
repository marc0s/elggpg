<?php
/**
 * Elgg GnuPG
 *
 * @package ElggPG
 */

$user = get_user_by_username(get_input('username'));

putenv("GNUPGHOME=".elggpg_get_gpg_home());
$gpg = new gnupg();

if (!elgg_is_logged_in() || !$user || !$user->canEdit()) {
	register_error(elgg_echo("elggpg:delete:error"));
	forward(REFERER);
}

$info = $gpg->deletekey($user->openpgp_publickey);
$user->openpgp_publickey = NULL;

if ($info) {
	system_message(elgg_echo("elggpg:deleted"));
} else {
	register_error(elgg_echo("elggpg:delete:error"));		
}

forward(REFERER);
