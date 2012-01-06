<?php
/**
 * Elgg GnuPG
 *
 * @package ElggPG
 */

function fp2keyid($fp) {
	return substr($fp, count($fp)-17, 16);
}

function import_report($info) {
	$yes = elgg_echo('yes');
	$no  = elgg_echo('no');
	$search  = "\\n";
	$replace = "<br />";
	return str_replace($search, $replace, elgg_echo("elggpg:import:report", array(
		$info['imported']        ? $yes : $no,
		$info['unchanged']       ? $yes : $no,
		$info['newuserids']      ? $yes : $no,
		$info['newsubkeys']      ? $yes : $no,
		$info['secretimported']  ? $yes : $no,
		$info['secretunchanged'] ? $yes : $no,
		$info['newsignatures']   ? $yes : $no,
		$info['skippedkeys']     ? $yes : $no,
		$info['fingerprint'],
	));
}

$user = get_user_by_username(get_input('username'));
$public_key = get_uploaded_file('public_key');

putenv("GNUPGHOME=".elggpg_get_gpg_home());
$gpg = new gnupg();

if (!elgg_is_logged_in() || !$user || !$user->canEdit() || $public_key === false) {
	register_error(elgg_echo("elggpg:upload:error"));
	forward(REFERER);
}


$info = $gpg->import($public_key);
$new_fp = $info['fingerprint'];

$user_fp = elgg_get_metadata(array(
	'guid' => $user->guid,
	'metadata_name' => 'openpgp_publickey',
));
$user_fp = $user_fp[0];
$access_id = ACCESS_LOGGED_IN;

if ($user_fp && $user_fp->value != $new_fp) {
	update_metadata($user_fp->id, $user_fp->name, $new_fp, 'text', $user->guid, $access_id);
	$info['imported'] = 1;
} elseif (!$userfp) {
	create_metadata($user->guid, "openpgp_publickey", $new_fp, 'text', $user->guid, $access_id);
	$info['imported'] = 1;
}

if ($info['imported']) {
	add_to_river('river/elggpg/update', 'addkey',$user->getGUID(), $user->getGUID());
	system_message(elgg_echo("elggpg:upload:imported"), array(fp2keyid($new_fp)));
	system_message(import_report($info)); 
} elseif ($info['unchanged']) {
	system_message(elgg_echo("elggpg:upload:unchanged"));		
	system_message(import_report($info));		
} else {
	system_message(import_report($info));		
}

forward(REFERER);
