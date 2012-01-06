<?php
/**
 * ElggPG river view.
 */

echo elgg_view('river/elements/layout', array(
	'item' => $vars['item'],
	'summary' => elgg_view('river/elggpg/summary', $vars),
));
