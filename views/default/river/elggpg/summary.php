<?php
/**
 * Short summary of the action that occurred
 *
 * @vars['item'] ElggRiverItem
 */

$item = $vars['item'];

$subject = $item->getSubjectEntity();
$object = $item->getObjectEntity();

$subject_link = elgg_view('output/url', array(
	'href' => $subject->getURL(),
	'text' => $subject->name,
	'class' => 'elgg-river-subject',
	'is_trusted' => true,
));

$object_link = elgg_view('output/url', array(
	'href' => "elggpg/owner/$object->username",
	'text' => elgg_echo('elggpg'),
	'class' => 'elgg-river-object',
	'is_trusted' => true,
));

echo elgg_echo("river:addkey:user:default", array($subject_link, $object_link));
