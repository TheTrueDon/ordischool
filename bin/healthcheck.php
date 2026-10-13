<?php
declare(strict_types=1);

$socket = @fsockopen('127.0.0.1', 8080, $errorCode, $errorMessage, 1.0);
if ($socket === false) {
    exit(1);
}
fclose($socket);
exit(0);
