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
		]
	]
];
?>
