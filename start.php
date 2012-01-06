<?php
/**
 * Elgg GnuPG plugin
 *
 * @package ElggPG
 */

elgg_register_event_handler('init', 'system', 'elggpg_init');

/**
 * GnuPG plugin initialization functions.
 */
function elggpg_init() {
	
	// Extend CSS
	elgg_extend_view('css/elgg', 'elggpg/css');
	
	// Register a page handler, so we can have nice URLs
	elgg_register_page_handler('elggpg', 'elggpg_page_handler');
	
	// Register entity type for search
	elgg_register_entity_type('object', 'elggpg');

	// Register a notification handler to encrypt messages
	register_notification_handler("email", "elggpg_email_notify_handler");
	
	elgg_extend_view("core/settings/account/email", "elggpg/viewkey", 1);
	
	// Actions
	$actions_path = elgg_get_plugins_path() . 'elggpg/actions/elggpg';
	elgg_register_action("elggpg/pubkey_upload", "$actions_path/pubkey_upload.php");
	elgg_register_action("elggpg/pubkey_delete", "$actions_path/pubkey_delete.php");
	elgg_register_action("elggpg/send", "$actions_path/send_encrypted.php");

	// add a GPG link to owner blocks
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'elggpg_owner_block_menu');

}

/**
 * Page handler
*/
function elggpg_page_handler($page) {

	$pages_dir = elgg_get_plugins_path() . 'elggpg/pages/elggpg';
	switch($page[0]) {
		case 'owner':
			include("$pages_dir/owner.php");
			break;
		case 'raw':
			set_input('username', $page[1]);
			include("$pages_dir/raw.php");
			break;
		default:
			return false;
	}
	return true;
}

function elggpg_get_gpg_home() {
	// try to find location of settings from environment file,
	// which means the gpg directory goes at the same level.
	$elgg_config = getenv("elgg_config");
	if ($elgg_config && is_dir(dirname($elgg_config)."/gpg")) {
		return dirname($elgg_config)."/gpg";
	}
	
	// otherwise create a gpg folder at the data folder
	// and store the keys there
	$gpg_path = elgg_get_data_path() . "gpg/";
	if (!file_exists($gpg_path)) {
		mkdir($gpg_path);
	}
	return $gpg_path;
}

/**
 * Send an internal message. "Fork" from the same function under the messages module. This one can save
 * a different message for the sent and received messages (each encrypted with a different key). Also, in
 * that case tags the messages so they can be processed differently than html messages (gpg data needs to be
 * respected a little bit).
 *
 * @param string $subject The subject line of the message
 * @param string $body The body of the mesage
 * @param int $send_to The GUID of the user to send to
 * @param int $from Optionally, the GUID of the user to send from
 * @param int $reply The GUID of the message to reply from (default: none)
 * @param true|false $notify Send a notification (default: true)
 * @param true|false $add_to_sent If true (default), will add a message to the sender's 'sent' tray
 * @return true|false Depending on success
 */

function elggpg_messages_send($subject, $body, $send_to, $from = 0, $reply = 0, $notify = true, $add_to_sent = true, $noclean=false) {

		global $messagesendflag;
		$messagesendflag = 1;

		global $messages_pm;
		if ($notify) {
			$messages_pm = 1;
		} else {
			$messages_pm = 0;
		}
		if($send_to == $from)
			$same = 1;
		else
			$same = 0;

		// If $from == 0, set to current user
		if ($from == 0)
			$from = (int) get_loggedin_user()->guid;

    		if($same){
			// Initialise a new ElggObject
			$message_to = new ElggObject();
			$message_sent = new ElggObject();
			// Tell the system it's a message
			$message_to->subtype = "messages";
			$message_sent->subtype = "messages";
			// Set its owner to the current user
			// $message_to->owner_guid = $_SESSION['user']->getGUID();
			$message_to->owner_guid = $send_to;
			$message_to->container_guid = $send_to;
			$message_sent->owner_guid = $from;
			$message_sent->container_guid = $from;
			// For now, set its access to public (we'll add an access dropdown shortly)
			$message_to->access_id = ACCESS_PUBLIC;
			$message_sent->access_id = ACCESS_PUBLIC;
			// Set its description appropriately
			$message_to->title = $subject;
			$message_to->description = $body;
			$message_sent->title = $subject;
			if ($noclean){
				$message_sent->description = $noclean;
        error_log("noclean");
        error_log($noclean);
			}else{
				$message_sent->description = $body;
        error_log("body");
        error_log($body);
			}
    			// set the metadata
	    		$message_to->toId = $send_to; // the user receiving the message
	    		$message_to->fromId = $from; // the user receiving the message
	    		$message_to->readYet = 0; // this is a toggle between 0 / 1 (1 = read)
	    		$message_to->hiddenFrom = 0; // this is used when a user deletes a message in their sentbox, it is a flag
	    		$message_to->hiddenTo = 0; // this is used when a user deletes a message in their inbox
	    		$message_sent->toId = $send_to; // the user receiving the message
	    		$message_sent->fromId = $from; // the user receiving the message
	    		$message_sent->readYet = 0; // this is a toggle between 0 / 1 (1 = read)
	    		$message_sent->hiddenFrom = 0; // this is used when a user deletes a message in their sentbox, it is a flag
	    		$message_sent->hiddenTo = 0; // this is used when a user deletes a message in their inbox

	    		$message_to->msg = 1;
	    		$message_sent->msg = 1;
	    	}else{
			$message_to = new ElggObject();
			$message_to->subtype = "messages";
			$message_to->owner_guid = $send_to;
                        $message_to->container_guid = $send_to;
			$message_to->access_id = ACCESS_PUBLIC;
			$message_to->title = $subject;
                        $message_to->description = $body;
			// set the metadata
                        $message_to->toId = $send_to; // the user receiving the message
                        $message_to->fromId = $from; // the user receiving the message
                        $message_to->readYet = 0; // this is a toggle between 0 / 1 (1 = read)
                        $message_to->hiddenFrom = 0; // this is used when a user deletes a message in their sentbox, it is a flag
                        $message_to->hiddenTo = 0; // this is used when a user deletes a message in their inbox
		}
		
		// Save the copy of the message that goes to the recipient
		$success = $message_to->save();

		// Save the copy of the message that goes to the sender
		if ($add_to_sent && $same) {
			$message_sent->save();
		}
		// Dropdown privileges (why?)
		$message_to->access_id = ACCESS_PRIVATE;
		$message_to->save();

		if ($add_to_sent && $same) {
			$message_sent->access_id = ACCESS_PRIVATE;
			$message_sent->save();
		}


	    	// if the new message is a reply then create a relationship link between the new message
	    	// and the message it is in reply to
		if($reply && $success && $same){
			$create_relationship = add_entity_relationship($message_sent->guid, "reply", $reply);
		}


		global $CONFIG;
			if ($noclean) {
			     $message_contents = $body;
			     $message_to->encrypted = 1;
			     if($same)
				$message_sent->encrypted = 1;
			}
			else
			     $message_contents = strip_tags($body);
           error_log($message_contents);
			if ($send_to != get_loggedin_user() && $notify)
			notify_user($send_to, get_loggedin_user()->guid, elgg_echo('messages:email:subject'),
				sprintf(
							elgg_echo('messages:email:body'),
							get_loggedin_user()->name,
							$message_contents,
							$CONFIG->wwwroot . "pg/messages/" . $user->username,
							get_loggedin_user()->name,
							$CONFIG->wwwroot . "mod/messages/send.php?send_to=" . get_loggedin_user()->guid
						)
			);

		$messagesendflag = 0;
		return $success;

}

function elggpg_email_notify_handler(ElggEntity $from, ElggUser $to, $subject, $message, array $params = NULL) {
        global $CONFIG;

        if (!$from) {
                throw new NotificationException(sprintf(elgg_echo('NotificationException:MissingParameter'), 'from'));
        }

        if (!$to) {
                throw new NotificationException(sprintf(elgg_echo('NotificationException:MissingParameter'), 'to'));
        }

        if ($to->email=="") {
                throw new NotificationException(sprintf(elgg_echo('NotificationException:NoEmailAddress'), $to->guid));
        }
	
        // Sanitise subject
        $subject = preg_replace("/(\r\n|\r|\n)/", " ", $subject); // Strip line endings
        // To
        $to = $to->email;

        // From
        $site = get_entity($CONFIG->site_guid);
        // If there's an email address, use it - but only if its not from a user.
        if ((isset($from->email)) && (!($from instanceof ElggUser))) {
                $from = $from->email;
        } else if (($site) && (isset($site->email))) {
                // Has the current site got a from email address?
                $from = $site->email;
        } else if (isset($from->url))  {
                // If we have a url then try and use that.
                $breakdown = parse_url($from->url);
                $from = 'noreply@' . $breakdown['host']; // Handle anything with a url
        } else {
                // If all else fails, use the domain of the site.
                $from = 'noreply@' . get_site_domain($CONFIG->site_guid);
        }

        if (is_callable('mb_internal_encoding')) {
                mb_internal_encoding('UTF-8');
        }
        $site = get_entity($CONFIG->site_guid);
        $sitename = $site->name;
        if (is_callable('mb_encode_mimeheader')) {
                $sitename = mb_encode_mimeheader($site->name,"UTF-8", "B");
        }

        $header_eol = "\r\n";
        if (
                (isset($CONFIG->broken_mta)) &&
                ($CONFIG->broken_mta)
        ) {
                // Allow non-RFC 2822 mail headers to support some broken MTAs
                $header_eol = "\n";
        }

        $from_email = "\"$sitename\" <$from>";
        if (strtolower(substr(PHP_OS, 0 , 3)) == 'win') {
                // Windows is somewhat broken, so we use a different format from header
                $from_email = "$from";
        }

        $headers = "From: $from_email{$header_eol}"
                . "Content-Type: text/plain; charset=UTF-8; format=flowed{$header_eol}"
                . "MIME-Version: 1.0{$header_eol}"
                . "Content-Transfer-Encoding: 8bit{$header_eol}";

        if (is_callable('mb_encode_mimeheader')) {
                $subject = mb_encode_mimeheader($subject,"UTF-8", "B");
        }

        // Format message
        $message = html_entity_decode($message, ENT_COMPAT, 'UTF-8'); // Decode any html entities
        $message = strip_tags($message); // Strip tags from message
        $message = preg_replace("/(\r\n|\r)/", "\n", $message); // Convert to unix line endings in body
        $message = preg_replace("/^From/", ">From", $message); // Change lines starting with From to >From
        $message = wordwrap($message);
        try {
            if (strpos($message, "-----BEGIN PGP MESSAGE-----") === false) {
                putenv("GNUPGHOME=".elggpg_get_gpg_home());
                $gpg = new gnupg();
                $gpg->addencryptkey($to);
                //$gpg = new Crypt_GPG(array('homedir' => elggpg_get_gpg_home()));
                //$gpg->addEncryptKey($to);
                $encmessage = $gpg->encrypt($message);
		if ($encmessage)
			$message = $encmessage;
            } else {
            }
        }
        catch (Exception $e) {
        }

        return mail($to, $subject, $message, $headers);
}

/**
 * Add a menu item to an ownerblock
 * 
 * @param string $hook
 * @param string $type
 * @param array  $return
 * @param array  $params
 */
function elggpg_owner_block_menu($hook, $type, $return, $params) {
	if (elgg_instanceof($params['entity'], 'user')) {
		$url = "elggpg/owner/{$params['entity']->username}";
		if ($params['entity'] == elgg_get_logged_in_user_entity()) {
			$item = new ElggMenuItem('elggpg', elgg_echo('elggpg:manage'), $url);
		} else {
			$item = new ElggMenuItem('elggpg', elgg_echo('elggpg:view'), $url);
		}
		$return[] = $item;
	}
	return $return;
}
