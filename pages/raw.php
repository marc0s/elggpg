<?php
  header("Content-type: text/plain");
  if (!isloggedin()) {
    forward();
  }

  putenv("GNUPGHOME=".elggpg_get_gpg_home());
  $gnupg = new gnupg();
	$user = get_user_by_username(get_input('username'));

	echo $gnupg->export($user->openpgp_publickey);
?>
