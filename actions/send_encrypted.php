<?php

	/**
	 * Elgg send a message action page
	 * 
	 * @package ElggMessages
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
	 * @author Curverider Ltd <info@elgg.com>
	 * @copyright Curverider Ltd 2008-2010
	 * @link http://elgg.com/
	 */
	 
	 // Make sure we're logged in (send us to the front page if not)
		if (!isloggedin()) forward();

		putenv("GNUPGHOME=".elggpg_get_gpg_home());
		$gpg = new gnupg();

	  // Get input data
		$title = get_input('title'); // message title
		$message_contents = get_input('message'); // the message
    $forward_url = "messages/inbox/".$_SESSION['user']->username;

		$send_to = get_input('send_to'); // this is the user guid to whom the message is going to be sent
		$reply = get_input('reply',0); // this is the guid of the message replying to
		try {
			$gpg->addencryptkey(get_loggedin_user()->openpgp_publickey);
			$message_contents_from = $gpg->encrypt(strip_tags($message_contents));
		}
		catch (Exception $e) {
			$message_contents_from = $message_contents;
		}
		
		$gpg->cleardecryptkeys();
		$gpg->addencryptkey(get_entity($send_to)->openpgp_publickey);
		$message_contents = $gpg->encrypt(strip_tags($message_contents));

	  // Cache to the session to make form sticky
		$_SESSION['msg_to'] = $send_to;
		
		if($title == ' '){
			$title = elgg_echo("elggpg:nosubject");
		}
		$_SESSION['msg_title'] = $title;
		$_SESSION['msg_contents'] = $message_contents;

		if (empty($send_to)) {
			register_error(elgg_echo("messages:user:blank"));
			forward($forward_url);
		}
		
		$user = get_user($send_to);
		if (!$user) {
			register_error(elgg_echo("messages:user:nonexist"));
			forward($forward_url);
		}

	// Make sure the message field, send to field and title are not blank
		if (empty($message_contents) || empty($title)) {
			register_error(elgg_echo("messages:blank"));
			forward($forward_url);
		}
		
	// Otherwise, 'send' the message 
	$result = elggpg_messages_send($title,$message_contents,$send_to,$send_to,$reply,true,false,true);
			
	// Save 'send' the message
		if (!$result) {
			register_error(elgg_echo("messages:error"));
			forward($forward_url);
		}

	// successful so uncache form values
		unset($_SESSION['msg_to']);
		unset($_SESSION['msg_title']);
		unset($_SESSION['msg_contents']);
			
	// Success message
		system_message(elgg_echo("messages:posted"));
	
	// Forward to the users inbox
		forward($forward_url);

?>
