<!doctype html>
<html>
<head>
	<title>Present Credential</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
	<h1>Present Credential</h1>
	<p>Scan to present your credential.</p>
	<p>Nothing will happen here when you present the credential; this is just for testing your client.</p>
<?php
$scheme = $_SERVER['REQUEST_SCHEME'];
if (!$scheme) $scheme = 'http';
$self_url = $scheme.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$vp_request_url = dirname($self_url).'/vp_request.php';
?>
	<p><a href="<?=$vp_request_url?>">
		<img src="qrcode.php?url=<?=urlencode($vp_request_url)?>" alt="QR Code for Presentation Request" border=0>
	</a></p>
	<p><a href=".">Back</a></p>
</body>
</html>
