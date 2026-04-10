<?php
$paypal = new \PayPal\Rest\ApiContext(
  new \PayPal\Auth\OAuthTokenCredential(
	$addons['custom9'],
	$addons['custom10']
  )
);

$paypal->setConfig(
    array(
      'mode' => paypalMode($addons['custom6'])
    )
);
?>
