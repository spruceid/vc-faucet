<?php
// http://phpqrcode.sourceforge.net/
include '/usr/share/phpqrcode/qrlib.php';
$url = @$_GET['url'];
if (!$url) throw new Error('Missing URL');
QRcode::png($url, FALSE, 'L', 8, 2);
?>
