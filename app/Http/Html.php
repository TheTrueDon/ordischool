<?php
declare(strict_types=1);

namespace OrdiSchool\Http;

final class Html
{
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function page(string $title, string $content): void
    {
        $safeTitle = self::escape($title);
        echo '<!doctype html><html lang="de"><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>' . $safeTitle . ' – OrdiSchool</title>';
        echo '<link rel="stylesheet" href="/assets/app.css"></head><body>';
        echo '<header class="topbar"><a class="brand" href="/">OrdiSchool</a><span>Technisches Grundgerüst v0.1.2</span></header>';
        echo '<main class="wrap">' . $content . '</main>';
        echo '<footer>Lokale Entwicklungsfassung · keine Schul- oder Schülerdaten</footer>';
        echo '</body></html>';
    }
}
