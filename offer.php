<?php
ini_set('display_errors', 1);
$id = @$_GET['id'];
$expires = @$_GET['expires'];
$hmac = @$_GET['hmac'];
$offering_id = @$_GET['offering'];
if (!$id || !$expires || !$hmac || !$offering_id) {
	header('HTTP/1.0 400 Bad Request');
	die('Missing request parameters');
}
$query = "id=$id&expires=$expires&offering=$offering_id";
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

$issue_to_subject = true;
$issue_credential = true;
switch ($offering['type']) {
case 'static_preissued':
	$issue_to_subject = false;
	$issue_credential = false;
	$filename = $offering['filename'];
	if (!$filename) {
		header('HTTP/1.0 500');
		die('Missing filename for static credential.');
	}
	$credential = json_decode(file_get_contents($filename));
	break;

case 'minimal':
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
	break;

case 'open_badge_credible':
	$issuer = $config['key_did'];
	$credential = (object)[
		'@context' => [
			'https://www.w3.org/2018/credentials/v1',
			'https://w3c-ccg.github.io/vc-ed/plugfest-1-2022/jff-vc-edu-plugfest-1-context.json'
		],
		'id' => $id,
		'type' => ['VerifiableCredential', 'OpenBadgeCredential'],
		'issuer' => (object)[
			'type' => 'Profile',
			'id' => $issuer,
			'name' => $offering['issuer_name']
		],
		'issuanceDate' => gmdate('Y-m-d\TH:i:s\Z'),
		'credentialSubject' => (object)[
			'type' => 'AchievementSubject',
			'achievement' => (object)[
				'type' => 'Achievement',
				'name' => 'I participated in a Credible Protocol Interaction during the JFF Plugfest #1 2022',
				'description' => 'This wallet can display this Open Badge 3.0',
				'criteria' => (object)[
					'type' => 'Criteria',
					'narrative' => 'Used Credible or compatible wallet to interactively receive a verifiable credential.'
				],
				'image' => 'https://demo.didkit.dev/2022/06/achievement.png'
			]
		]
	];
	break;

default:
	header('HTTP/1.0 500');
	die('Unknown credential offering type');
}

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
if ($issue_to_subject) {
	$subject_id = @$_POST['subject_id'];
	if (!$subject_id) {
		header('HTTP/1.0 400 Bad Request');
		die('Missing subject_id in request');
	}
	$credential->credentialSubject->id = $subject_id;
}
if ($issue_credential) {
	require_once 'DIDKit.php';
	$verification_method = $config['vm'];
	$options = [
		'proofPurpose' => 'assertionMethod',
		'verificationMethod' => $verification_method
	];
	$key_filename = $config['key_filename'];
	$vc = DIDKit::issueCredential($credential, $options, $key_filename);
	print $vc;
} else {
	print json_encode($credential, JSON_PRETTY_PRINT);
}

header('Content-Type: application/ld+json');
