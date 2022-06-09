<?php
$config = [
	'hmac_secret' => '000000000000000000000000000000000000',
	'key_did' => 'did:example:changeme',
	'vm' => 'did:example:changeme#key-1',
	'trusted_issuers' => [
	],
	'key_filename' => $_SERVER['HOME'].'/.config/didkit/key.jwk',
	'default_offering' => 'minimal',
	'offerings' => [
		'minimal' => [
			'button_name' => 'Minimal',
			'type' => 'minimal'
		],
		'open_badge_credible' => [
			'button_name' => 'Open Badge Credible Protocol Interaction',
			'issuer_name' => 'Example Demo Server',
			'type' => 'open_badge_credible',
		]
	],
];
?>
