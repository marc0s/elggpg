<?php
/**
 * Fingerprint output view
 * 
 * @package ElggPG
 */

$fingerprint = elgg_extract('value', $vars);

for ($i=0; $i<strlen($fingerprint); $i++) {
	echo $fingerprint[$i];
	if ($i % 4 == 3) {
		echo " ";
	}
}
