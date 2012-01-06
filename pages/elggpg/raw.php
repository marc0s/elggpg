<?php
/**
 * GPG Public Key download
 *
 * @package ElggPG
 */
  
if (!elgg_is_logged_in()) {
	forward();
}

putenv("GNUPGHOME=" . elggpg_get_gpg_home());
$gnupg = new gnupg();

header("Content-type: text/plain");
$user = get_user_by_username(get_input('username'));
echo $gnupg->export($user->openpgp_publickey);
