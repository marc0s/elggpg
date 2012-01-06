<?php

	/**
	 * Elgg profile plugin upload new user icon action
	 * 
	 * @package ElggProfile
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
	 * @author Curverider Ltd <info@elgg.com>
	 * @copyright Curverider Ltd 2008-2010
	 * @link http://elgg.com/
	 */

	gatekeeper();
	
  putenv("GNUPGHOME=".elggpg_get_gpg_home());
  $gpg = new gnupg();

  $user = $_SESSION['user'];

  function fp2keyid($fp) {
    return substr($fp,  count($fp)-17, 16);
  }
  function import_report($info) {
    $report = str_replace("\\n","<br />",elgg_echo("elggpg:import:report"));
    return sprintf($report,$info['imported'], $info['unchanged'], 
                   $info['newuserids'], $info['newsubkeys'], $info['secretimported'],
                   $info['secretunchanged'], $info['newsignatures'], 
                   $info['skippedkeys'], $info['fingerprint']);
  }

  // If we were given a correct icon
  if (isloggedin() && $user && $user->canEdit())
  {
    $text = get_uploaded_file('public_key');
    if ($text !== false) 
    {

    	$info = $gpg->import($text);
      $new_fp = $info['fingerprint'];
      error_log(print_r($info,true));
      $user_fp = elgg_get_metadata(array(
              'guid' => $user->guid,
              'metadata_name' => 'openpgp_publickey',
             ));
      $user_fp = $user_fp[0];
      $access_id = ACCESS_LOGGED_IN;

      if ($user_fp) 
      {
        if ($user_fp->value != $new_fp)
        {
          update_metadata($user_fp->id, $user_fp->name, $new_fp, 'text', $user->guid, $access_id);
          $info['imported'] = 1;
        }
      }
      else
      {
        create_metadata($user->guid, "openpgp_publickey", $new_fp, 'text', $user->guid, $access_id);
        $info['imported'] = 1;
      }

    	if ($info['imported']) {
        
    		add_to_river('river/elggpg/update','addkey',$user->getGUID(),$user->getGUID());
    		system_message(sprintf(elgg_echo("elggpg:upload:imported"),fp2keyid($new_fp)));
    		system_message(import_report($info)); 
      }
      elseif ($info['unchanged'])  
      {
    	  system_message(elgg_echo("elggpg:upload:unchanged"));		
    	  system_message(import_report($info));		
    	}
      else 
      {
    	  system_message(import_report($info));		
      }
    }
    else 
    {
    	register_error(elgg_echo("elggpg:upload:error"));		
    } 
  }
  	
  forward(REFERER);

?>
