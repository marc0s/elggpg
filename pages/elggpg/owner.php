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
	$form_vars = array(
		'enctype' => "multipart/form-data",
		'class' => 'mtl',
	);
	$body_vars = array(
		'user' => $owner,
	);
	
	$content .= elgg_view_form("elggpg/pubkey_upload", $form_vars, $body_vars);
	elgg_register_menu_item('title', array(
			'name' => 'elggpg_delete',
			'href' => 'action/elggpg/pubkey_delete?username='.$owner->username,
			'text' => elgg_echo('delete'),
			'link_class' => 'elgg-button elgg-button-action',
			'is_action' => true,
			'confirm' => elgg_echo('elggpg:delete:confirm'),
	));
}

elgg_register_menu_item('title', array(
		'name' => 'elggpg_download',
		'href' => 'elggpg/raw/'.$owner->username,
		'text' => elgg_echo('elggpg:download'),
		'link_class' => 'elgg-button elgg-button-action',
));

$body = elgg_view_layout('content', array(
	'title' => $title,
	'content' => $content,
	'filter' => '',
));

echo elgg_view_page($title, $body);
