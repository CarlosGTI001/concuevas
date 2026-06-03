<?php
declare(strict_types=1);

function setting(string $key, string $default = ''): string
{
    static $cache = null;

    if (!is_array($cache)) {
        $cache = [];
        $stmt = db()->query('SELECT `key`, `value` FROM site_settings');
        foreach ($stmt->fetchAll() as $row) {
            $cache[$row['key']] = (string) $row['value'];
        }
    }

    return $cache[$key] ?? $default;
}

function site_name(): string
{
    return setting('site_name', 'Construcciones Cuevas');
}

function site_tagline(): string
{
    return setting('site_tagline', 'Contruccion & Arquitectura');
}
