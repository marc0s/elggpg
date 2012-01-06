<?php
/**
 * View key view
 *
 * @uses $vars['user'] The user entity
 * 
 */

// user is passed to view and set by caller (normally the page editicon)
$currentuser = $vars['user'];


// new class
putenv("GNUPGHOME=" . elggpg_get_gpg_home());
$gnupg = new gnupg();

$user_fp = elgg_get_metadata(array(
            'guid' => $currentuser->guid,
            'metadata_name' => 'openpgp_publickey',
           ));
$user_fp = $user_fp[0];

try {
	$info = $gnupg->keyinfo($user_fp->value);
	
	$name    = $info[0]['uids'][0]['name'];
	$comment = $info[0]['uids'][0]['comment'];
	$email = $info[0]['uids'][0]['email'];
	$fingerprint = $info[0]['subkeys'][0]['fingerprint'];
	$raw_url = elgg_get_site_url(). '/elggpg/raw/' . $currentuser->username;
	
	if (strlen($fingerprint) < 1) {
		throw new Exception();
	}
} catch (Exception $e) {
	echo '<p>'. elgg_echo("elggpg:nopublickey") . '</p>';
	return false;
}

echo "<strong>$fingerprint</strong>";
echo "<a href=\"$raw_url\"> [".elgg_echo('elggpg:download')."]</a><br/>";

foreach ($info[0]['subkeys'] as $subkey) {
	if ($subkey['can_encrypt']) {
		
	}

	echo elgg_echo('elggpg:created').": ".date('d M Y', $subkey['timestamp'])."<br />";
	
	if ($subkey['expires']) {
		echo elgg_echo('elggpg:expires').": ".date('d Y M', $subkey['expires'])."<br/>";
	} else {
		echo elgg_echo('elggpg:expires').": ".elgg_echo('elggit gpg:expires:never')."<br/>";
	}
	
	echo elgg_echo("elggpg:keyid").": ".$subkey["keyid"]."<br />";

}
