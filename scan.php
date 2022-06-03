<!doctype html>
<html>
	<head>
		<title>Credential Offer</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
	</head>
<body>
	<h1>Credential Offer</h1>
<?php
require_once 'config.php';

$get_credential = @$_POST['get_credential'];
$offering = null;
if (is_array($get_credential)) {
	foreach ($get_credential as $key => $_value) {
		if ($offering) die('Only one credential may be issued per request.');
		$offering = $key;
	}
}
if (!$offering) $offering = $config['default_offering'];

$id = 'urn:uuid:'.uuid_create();
$expires = time() + 60*15;
$key = $config['hmac_secret'];
$query = "id=$id&expires=$expires&offering=$offering";
$hmac = hash_hmac('sha256', $query, $key);
$query .= "&hmac=$hmac";
$origin = @$_SERVER['HTTP_ORIGIN'];
if (!$origin) {
	$scheme = $_SERVER['REQUEST_SCHEME'];
	if (!$scheme) $scheme = 'http';
	$host = $_SERVER['HTTP_HOST'];
	$origin = $scheme.'://'.$host;
}
$self_url = $origin.$_SERVER['REQUEST_URI'];
$offer_url = dirname($self_url).'/offer.php?'.$query;
?>
	<p>Scan with credential wallet:</p>
	<p><a href="<?=$offer_url?>">
		<img src="qrcode.php?url=<?=urlencode($offer_url)?>" alt="QR Code for Credential Offer" border=0>
	</a></p>
	<p><a href=".">Back</a></p>
</body>
</html>
