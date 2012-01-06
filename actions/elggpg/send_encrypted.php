<?php
/**
* Ssend a message action
* 
* @package ElggMessages
*/

$subject = strip_tags(get_input('subject'));
$body = get_input('body');
$recipient_guid = get_input('recipient_guid');

elgg_make_sticky_form('messages');

//$reply = get_input('reply',0); // this is the guid of the message replying to

if (!$recipient_guid) {
	register_error(elgg_echo("messages:user:blank"));
	forward("messages/compose");
}

$user = get_user($recipient_guid);
if (!$user) {
	register_error(elgg_echo("messages:user:nonexist"));
	forward("messages/compose");
}

// Make sure the message field, send to field and title are not blank
if (!$body || !$subject) {
	register_error(elgg_echo("messages:blank"));
	forward("messages/compose");
}

// Otherwise, encrypt and 'send' the message 

putenv("GNUPGHOME=".elggpg_get_gpg_home());
$gpg = new gnupg();

try {
	$gpg->addencryptkey(elgg_get_logged_in_user_entity()->openpgp_publickey);
	if ($encrbody = $gpg->encrypt(strip_tags($body))) {
		$body_from = $encrbody;
	}
} catch (Exception $e) {
}
$body_from = $body;
	
$gpg->cleardecryptkeys();

// FIXME Encrypting do not works
try {
	$gpg->addencryptkey($user->openpgp_publickey);
	if ($encrbody = $gpg->encrypt(strip_tags($body))) {
		$body_to = $encrbody;
	}
} catch (Exception $e) {
}
$body_to = $body;

// TODO: messages_send saves two copies of the message. In previous version of the
// elggpg plugin, this function was overriden to allow save both versions encrypted
// with its owner key. Nowadays there is only one version saved, the 'sent' one.
$result = messages_send($subject, $body_to, $recipient_guid, 0, $reply);

// Save 'send' the message
if (!$result) {
	register_error(elgg_echo("messages:error"));
	forward("messages/compose");
}

elgg_clear_sticky_form('messages');
	
system_message(elgg_echo("messages:posted"));

forward('messages/inbox/' . elgg_get_logged_in_user_entity()->username);
