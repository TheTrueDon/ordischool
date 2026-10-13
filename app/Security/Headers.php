<?php
declare(strict_types=1);

namespace OrdiSchool\Security;

use OrdiSchool\Config;

final class Headers
{
    public static function send(Config $config): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: no-referrer');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header("Content-Security-Policy: default-src 'self'; style-src 'self'; img-src 'self' data:; form-action 'self'; base-uri 'self'; frame-ancestors 'none'; object-src 'none'");
        header('Cache-Control: no-store, private');
        header('Pragma: no-cache');
        if ($config->environment === 'production') {
            header('Strict-Transport-Security: max-age=31536000');
        }
    }
}
