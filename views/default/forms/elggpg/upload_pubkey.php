<?php

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
