<!doctype html>
<html>
	<head>
		<title>Get Credential</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
	</head>
<body>
	<h1>Get Credential</h1>
	<form action="scan.php" method="post">
		<p>Click below to get your credential offer. The offer will be valid for 15 minutes. If it expires, you can return to this page to get another one.</p>
<ul>
<?php
require_once 'config.php';
foreach ($config['offerings'] as $offering_id => $offering) {
?>
		<li><input type=submit name="get_credential[<?=htmlspecialchars($offering_id)?>]" value="<?=htmlspecialchars($offering['button_name'])?>"></li>
<?php
}
?>
</ul>
	</form>
	<p><a href=".">Back</a></p>
</body>
</html>
