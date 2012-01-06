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
elgg_load_library('elggpg');
$info = elggpg_keyinfo($currentuser);

if (!$info) {
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
		<dd>{$info['name']}</dd>
	</dl>
	<dl>
		<dt>E-mail</dt>
		<dd>{$info['email']}</dd>
	</dl>
	<dl>
		<dt>Comment</dt>
		<dd>{$info['comment']}</dd>
	</dl>
	<dl>
		<dt>Fingerprint</dt>
		<dd>{$info['fingerprint']}</dd>
	</dl>
	<br/>
	<h3>Subkeys</h3>
	<table class="elgg-table mtm">
	<th>$key_id</th><th>$type</th><th>$created</th><th>$expires</th>
HTML;

foreach ($info['subkeys'] as $subkey) {
	
	$keyid = $subkey["keyid"];

	$created = date('d M Y', $subkey['created']);
	
	if ($subkey['expires']) {
		$expires = date('d M Y', $subkey['expires']);
	} else {
		$expires = elgg_echo('elggpg:expires:never');
	}
	
	$type = elgg_echo('elggpg:type:'.$subkey['type']);

	echo <<<HTML
	<tr><td>$keyid</td><td>$type</td><td>$created</td><td>$expires</td></tr>
HTML;
	
}

echo <<<HTML
</table>
</div>
HTML;
