<?php
ini_set('display_errors', 1);
$id = @$_GET['id'];
$expires = @$_GET['expires'];
$hmac = @$_GET['hmac'];
$offering_id = @$_GET['offering'];
if (!$id || !$expires || !$hmac) {
	header('HTTP/1.0 400 Bad Request');
	die('Missing request parameters');
}
$query = "id=$id&expires=$expires";
require_once 'config.php';
if (!$offering_id) $offering_id = $config['default_offering'];
$offering = $config['offerings'][$offering_id];
if (!$offering) {
	header('HTTP/1.0 400 Bad Request');
	die('Unable to find offering.');
}
$key = $config['hmac_secret'];
if (time() > $expires) {
	header('HTTP/1.0 410 Gone');
	die('Offer Expired');
}
$hmac_expected = hash_hmac('sha256', $query, $key);
if (!hash_equals($hmac_expected, $hmac)) {
	header('HTTP/1.0 400 Bad Request');
	die('Invalid HMAC');
}
header('Content-Type: application/json');
$credential_expires = time() + 60*60*24*30;
$issuer = $config['key_did'];
$credential = (object)[
	'@context' => ['https://www.w3.org/2018/credentials/v1'],
	'id' => $id,
	'type' => 'VerifiableCredential',
	'issuer' => $issuer,
	'issuanceDate' => gmdate('Y-m-d\TH:i:s\Z'),
	'expirationDate' => gmdate('Y-m-d\TH:i:s\Z', $credential_expires),
	'credentialSubject' => (object)[
	]
];
$offer = (object)[
	'type' => 'CredentialOffer',
	'credentialPreview' => $credential,
	'expires' => gmdate('Y-m-d\TH:i:s\Z', $expires)
];
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	print json_encode($offer, JSON_PRETTY_PRINT);
	exit;
}

// Client accepts offer
require_once 'DIDKit.php';
$subject_id = $_POST['subject_id'];
if (!$subject_id) {
	header('HTTP/1.0 400 Bad Request');
	die('Missing subject_id in request');
}
$verification_method = $config['vm'];
$credential->credentialSubject->id = $subject_id;
$options = [
	'proofPurpose' => 'assertionMethod',
	'verificationMethod' => $verification_method
];
$key_filename = $config['key_filename'];
$vc = DIDKit::issueCredential($credential, $options, $key_filename);
print $vc;

header('Content-Type: application/ld+json');
