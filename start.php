<?php
        /**
         * Elgg gpg management
         * 
         * @package ElggGpg
         * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
         * @author Pablo Martin
         * @copyright Pablo Martin
         */


/**
 * Get the gpg home folder
*/
function elggpg_get_gpg_home()
{
	global $CONFIG;
	// try to find location of settings from environment file,
	// which means the gpg directory goes at the same level.
	$elgg_config = getenv("elgg_config");
	if ($elgg_config && is_dir(dirname($elgg_config)."/gpg"))
		return dirname($elgg_config)."/gpg";
	// otherwise create a gpg folder at the data folder
	// and store the keys there
	$gpg_path = $CONFIG->dataroot . "gpg";
	if (!file_exists($gpg_path))
		mkdir($gpg_path);
	return $gpg_path;
}


/**
 * Page handler
*/
function elggpg_handler($page) {
	global $CONFIG;
	$pages = dirname(__FILE__) . '/pages/';
	switch($page[0]) {
		case 'owner':
			include("$pages/owner.php");
			break;
		case 'raw':
			set_input('username', $page[1]);
			include("$pages/raw.php");
			break;
		default:
      echo "empty door";
		//	set_input('user',get_user_by_username($page[0])->getGUID());
		//	include("$pages/show.php");
	}
}

/**
 * Init engine hooks
*/
function elggpg_init() {
	global $CONFIG;

	// Page handler
	include($CONFIG->pluginspath . "elggpg/mailhandler.php");
	register_page_handler('elggpg','elggpg_handler');

	// Register entity type for search
	elgg_register_entity_type('object', 'elggpg');


	// Keys
	register_notification_handler("email", "elggpg_email_notify_handler");
	register_action("elggpg/pub_key_upload",false,$CONFIG->pluginspath . "elggpg/actions/pub_key_upload.php");
	register_action("elggpg/send",false,$CONFIG->pluginspath . "elggpg/actions/send_encrypted.php");

	// Extend profile menu   
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'elggpg_owner_block_menu');

	elgg_extend_view('css/elgg','elggpg/css');
  elgg_extend_view("core/settings/account/email", "elggpg/viewkey", 1);

}

register_elgg_event_handler('init','system','elggpg_init');

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
    if ($params['entity'] == elgg_get_logged_in_user_entity()) 
    {
		  $item = new ElggMenuItem('elggpg', elgg_echo('elggpg:manage'), $url);
    } 
    else 
    {
		  $item = new ElggMenuItem('elggpg', elgg_echo('elggpg:view'), $url);
    }
		$return[] = $item;
	} 

	return $return;
}


?>
