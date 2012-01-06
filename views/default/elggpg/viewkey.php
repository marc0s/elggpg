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
	
	if (strlen($fingerprint) < 1) {
		throw new Exception();
	}
	
} catch (Exception $e) {
	echo '<p>'. elgg_echo("elggpg:nopublickey") . '</p>';
	return false;
}

$key_id  = elgg_echo('elggpg:keyid');
$type    = elgg_echo('elggpg:type');
$created = elgg_echo('elggpg:created');
$expires = elgg_echo('elggpg:expires');

echo <<<HTML
<div class="elgg-elggpg elgg-output">
	<dl>
		<dt>Name</dt>
		<dd>$name</dd>
	</dl>
	<dl>
		<dt>E-mail</dt>
		<dd>$email</dd>
	</dl>
	<dl>
		<dt>Comment</dt>
		<dd>$comment</dd>
	</dl>
	<dl>
		<dt>Fingerprint</dt>
		<dd>$fingerprint</dd>
	</dl>
	<br/>
	<h3>Subkeys</h3>
	<table class="elgg-table mtm">
	<th>$key_id</th><th>$type</th><th>$created</th><th>$expires</th>
HTML;

foreach ($info[0]['subkeys'] as $subkey) {
	
	$keyid = $subkey["keyid"];

	$created = date('d M Y', $subkey['timestamp']);
	
	if ($subkey['expires']) {
		$expires = date('d M Y', $subkey['expires']);
	} else {
		$expires = elgg_echo('elggpg:expires:never');
	}
	
	if ($subkey['can_encrypt']) {
		$type = elgg_echo('elggpg:encrypt');
	} elseif ($subkey['can_decrypt']) {
		$type = elgg_echo('elggpg:decrypt');
	} else {
		$type = elgg_echo('elggpg:encrypt') . " & " . elgg_echo('elggpg:decrypt');
	}
	
	echo <<<HTML
	<tr><td>$keyid</td><td>$type</td><td>$created</td><td>$expires</td></tr>
HTML;
	
}

echo <<<HTML
</table>
</div>
HTML;
