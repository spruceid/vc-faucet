<?php
$domain = $_SERVER['SERVER_NAME'];
$challenge = uuid_create();
$credential_query = [
	'reason' => 'Sign in',
	'example' => [
		'@context' => [
			'https://www.w3.org/2018/credentials/v1'
		],
		'type' => 'VerifiableCredential'
	]
];
$query = [
	[
		'type' => 'QueryByExample',
		'credentialQuery' => $credential_query
	]
];
$vp_request = (object)[
	'type' => 'VerifiablePresentationRequest',
	'query' => $query,
	'challenge' => $challenge,
	'domain' => $domain,
];
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	print json_encode($vp_request, JSON_PRETTY_PRINT);
	exit;
}

// Client presents credential
$presentation = $_POST['presentation'];
if (!$presentation) {
	header('HTTP/1.0 400 Bad Request');
	die('Missing presentation in request');
}
$verify_options = [
	'challenge' => $challenge,
	'domain' => $domain,
	'proofPurpose' => 'authentication'
];
require_once 'DIDKit.php';
try {
	DIDKit::verifyPresentation($presentation, $verify_options);
} catch(\Exception $e){
	header('HTTP/1.0 400 Bad Request');
	die('Unable to verify presentation: '.$e->getMessage());
}
error_log($presentation);
$vp = json_decode($presentation);
$vc = $vp->verifiableCredential;
if (!$vc) {
	header('HTTP/1.0 400 Bad Request');
	error_log('Unable to find credential');
	die('Unable to find credential');
}
try {
	DIDKit::verifyCredential($vc, $verify_options);
} catch(\Exception $e){
	header('HTTP/1.0 400 Bad Request');
	error_log('Unable to verify credential: '.$e->getMessage());
	die('Unable to verify credential: '.$e->getMessage());
}
require_once 'config.php';
if ($vc->issuer != $config['key_did']
  && !in_array($vc->issuer, $config['trusted_issuers'])) {
	header('HTTP/1.0 400 Bad Request');
	error_log('Untrusted issuer');
	die('Untrusted issuer');
}
if ($vp->holder != $vc->credentialSubject->id) {
	header('HTTP/1.0 400 Bad Request');
	error_log('Credential subject does not match holder');
	die('Credential subject does not match holder');
}
echo 'Ok';
?>
