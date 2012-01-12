<?php
/**
 * GPG Public Key download
 *
 * @package ElggPG
 */

$user = get_user_by_username(get_input('username'));
if (!elgg_is_logged_in() || !$user) {
	forward();
}

header("Pragma: public");
header("Content-type: application/pgp-keys");
header("Content-Disposition: attachment; filename=\"{$user->username}.asc\"");

ob_clean();
flush();

elgg_load_library('elggpg');
echo elggpg_export_key($user);
exit();
