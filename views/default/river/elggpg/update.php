<?php

	$performed_by = get_entity($vars['item']->subject_guid); // $statement->getSubject();
  $string = "".$vars['item'];

	if ($performed_by) {
	  $url = "<a href=\"{$performed_by->getURL()}\">{$performed_by->name}</a>";
    $riverstr =  elgg_echo("elggpg:river:".$vars['item']->action_type);

    $user = get_entity($vars['item']->object_guid);
    if ($user) {
	    $keyurl = "<a href=\"{$CONFIG->url}elggpg/owner/{$user->username}\">gpg</a>";
      $riverstr = str_replace("gpg", $keyurl, $riverstr);
    }

	  $string .= sprintf($riverstr, $url);
	}
	echo $string;
?>
