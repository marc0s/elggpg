<?php
/**
 * Upload a gpg key page
 *
 * @package ElggPG
 */

$owner = elgg_get_page_owner_entity();
if (!$owner || !elgg_instanceof($owner, 'user')) {
	forward();
}

elgg_push_breadcrumb(elgg_echo('members'), "members");
elgg_push_breadcrumb($owner->name, $owner->getURL());
elgg_push_breadcrumb(elgg_echo('elggpg:manage'));

$title = elgg_echo("elggpg:manage:header");
$content = elgg_view("elggpg/viewkey", array('user' => $owner));

if($owner->guid == elgg_get_logged_in_user_guid()) {
	$content .= elgg_view_form("elggpg/pubkey_upload", array('enctype' => "multipart/form-data"), array());
}

$body = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter' => '',
));

echo elgg_view_page($title, $body);
