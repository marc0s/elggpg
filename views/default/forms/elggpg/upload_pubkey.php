<?php
/**
 * Upload public key form
 *
 * @uses $vars['user'] The user entity
 * 
 */

echo elgg_view('input/hidden', array('name' => 'username', 'value' => $vars['user']->username));

echo "<label>" . elgg_echo("elggpg:upload") . "</label>";
echo elgg_view('input/file', array('name' => 'public_key'));

echo elgg_view('input/submit', array('value' => elgg_echo("upload")));
