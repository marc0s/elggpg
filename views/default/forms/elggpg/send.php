<?php
	$currentuser = $vars['currentuser'];
?>

<div>
  <h2><?php echo elgg_echo('elggpg:sendamessage'); ?></h2>
  <form action="<?php echo $vars['url']; ?>action/elggpg/send" method="post" enctype="multipart/form-data">
    <?php echo elgg_view('input/securitytoken'); ?>
    <input type="hidden" name="username" value="<?php echo $currentuser->username; ?>" />
    <input type="hidden" name="send_to" value="<?php echo $currentuser->getGUID(); ?>" />
    
    <p><label><?php echo elgg_echo("messages:title"); ?>: <br /><input type='text' name='title' value='<?php echo $msg_title; ?>' class="input-text" /></label></p>
    <p class="longtext_editarea"><label><?php echo elgg_echo("messages:message"); ?>: <br />
    <?php
      echo elgg_view("input/plaintext", array(
        "name" => "message",
        "value" => "",
      ));
    ?>
    </label></p>
    <p><input type="submit" class="elgg-button elgg-button-submit" value="<?php echo elgg_echo("messages:fly"); ?>" /></p>
  </form>
</div>

