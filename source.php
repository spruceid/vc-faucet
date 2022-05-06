<?php
header('Content-type: application/gzip');
header('Content-Disposition: attachment; filename="vc-faucet.tar.gz"');
passthru('tar cz --exclude=".*.sw*" --exclude=.git --exclude=config.php .');
?>
