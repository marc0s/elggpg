<?php

function elggpg_email_notify_handler(ElggEntity $from, ElggUser $to, $subject, $message, array $params = NULL) {
  global $CONFIG;
  
  if (!$from)
    throw new NotificationException(sprintf(elgg_echo('NotificationException:MissingParameter'), 'from'));
  
  if (!$to) 
    throw new NotificationException(sprintf(elgg_echo('NotificationException:MissingParameter'), 'to'));
  
  if ($to->email=="") 
    throw new NotificationException(sprintf(elgg_echo('NotificationException:NoEmailAddress'), $to->guid));
  
  // Sanitise subject
  $subject = preg_replace("/(\r\n|\r|\n)/", " ", $subject); // Strip line endings
  // To
  $to = $to->email;
  
  // From
  $site = get_entity($CONFIG->site_guid);
  // If there's an email address, use it - but only if its not from a user.
  if ((isset($from->email)) && (!($from instanceof ElggUser))) 
  {
    $from = $from->email;
  } 
  else if (($site) && (isset($site->email))) 
  {
    // Has the current site got a from email address?
    $from = $site->email;
  } 
  else if (isset($from->url))  
  {
    // If we have a url then try and use that.
    $breakdown = parse_url($from->url);
    $from = 'noreply@' . $breakdown['host']; // Handle anything with a url
  } 
  else 
  {
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

  $touser_fp = elgg_get_metadata(array(
          'guid' => $touser->guid,
          'metadata_name' => 'openpgp_publickey',
         ));
  $touser_fp = $touser_fp[0];

  try 
  {
    if (strpos($message, "-----BEGIN PGP MESSAGE-----") === false) 
    {
      putenv("GNUPGHOME=".elggpg_get_gpg_home());
      $gpg = new gnupg();
      $gpg->addencryptkey($touser_fp->value);
      $encmessage = $gpg->encrypt($message);

      if ($encmessage)
  	    $message = $encmessage;
    }
  }
    catch (Exception $e) {
  }
  
  return mail($to, $subject, $message, $headers);
}

?>
