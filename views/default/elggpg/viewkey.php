<?php
/**
 * External pages menu
 *
 * @uses $vars['user'] The user entity
 * @uses $vars['url']  The site url
 */

// user is passed to view and set by caller (normally the page editicon)
$currentuser = $vars['user'];


// new class
putenv("GNUPGHOME=".elggpg_get_gpg_home());
$gnupg = new gnupg();

$user_fp = elgg_get_metadata(array(
            'guid' => $currentuser->guid,
            'metadata_name' => 'openpgp_publickey',
           ));
$user_fp = $user_fp[0];

$access_id = ACCESS_LOGGED_IN;
$has_key = false;

try {
  echo "<p>";
  $info = $gnupg->keyinfo($user_fp->value);
  $fingerprint = $info[0]['subkeys'][0]['fingerprint'];




  if (strlen($fingerprint) > 1) {
    echo "<b>".$fingerprint."</b>";
    echo "<a href='".$vars['url'].'pg/elggpg/raw/'.$currentuser->username."'> [".elgg_echo('elggpg:download')."]</a><br/>";
    $subkeys = $info[0]['subkeys'];
    foreach ($subkeys as $subkey) {
      if ($subkey['can_encrypt'])

         echo $info[0]['uids'][0]["name"] ? elgg_echo("elggpg:name").": ".$info[0]["uids"][0]["name"]."<br />" : "";
         echo $info[0]['uids'][0]['email'] ? elgg_echo("elggpg:email").": ".$info[0]["uids"][0]["email"]."<br />" : "";
         echo $info[0]['uids'][0]['comment'] ? elgg_echo("elggpg:comment").": ".$info[0]["uids"][0]["comment"]."<br />" : "";

         echo elgg_echo('elggpg:created').": ".date('d M Y',$subkey['timestamp'])."<br />";
         echo elgg_echo('elggpg:expires').": ";
         if ($subkey['expires']) 
           echo date('d Y M',$subkey['expires'])."<br/>";
         else
           echo elgg_echo('elggpg:expires:never')."<br/>";
         echo $info[0]['subkeys'][0]['keyid'] ? elgg_echo("elggpg:keyid").": ".$info[0]["subkeys"][0]["keyid"]."<br />" : "";

    }
    echo "</p>";

	  $has_key = true;
  }
  else
    echo elgg_echo("elggpg:nopublickey");
}
catch (Exception $e) {
  echo elgg_echo("elggpg:nopublickey");
}

if ($currentuser == $_SESSION['user']) {
?>
<div id="elggpg-upload">
	<form action="<?php echo $vars['url']; ?>action/elggpg/pub_key_upload" method="post" enctype="multipart/form-data">
	<?php echo elgg_view('input/securitytoken'); ?>
	<input type="hidden" name="username" value="<?php echo $currentuser->username; ?>" />
	<div>
    <label><?php echo elgg_echo("elggpg:upload"); ?></label><br />
		<?php
			echo elgg_view("input/file",array('name' => 'public_key'));
		?>
		<input type="submit" class="elgg-button elgg-button-submit" value="<?php echo elgg_echo("upload"); ?>" />
    <br />
	</div>
	</form>
</div>
<br />
<?php
  if ($has_key) {
  	echo elgg_view('elggpg/forms/send',array('currentuser'=>$currentuser));
  }
  
  try {
    $gnupg->addencryptkey($user_fp->value);
    echo "<br /><div><label>".elgg_echo("elggpg:messageforyou")."</label><br />";
    echo "<pre class=\"pgparmor\">".$gnupg->encrypt("just for you! sign this message to validate your key")."</pre>";
    echo "</div>";
  }
  catch (Exception $e) {
  }
}
