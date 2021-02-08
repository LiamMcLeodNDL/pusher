<?php

declare(strict_types=1);

namespace ApiClients\Client\Pusher;

use Jean85\PrettyVersions;

class ApiSettings
{
    /**
     * Create Pusher compatible version.
     *
     * @param  string $version
     * @return string
     */
    public static function getVersion(string $version = ''): string
    {
        if ($version === '') {
            $version = PrettyVersions::getVersion('api-clients/pusher')->getFullVersion();
        }

        list($version, $hash) = \explode('@', $version);

        if (\strpos($version, 'dev') !== false) {
            return '0.0.1-' . $hash;
        }

        return $version;
    }

    /**
     * Create WebSocket URL for given App ID.
     *
     * @param  string $appId
     * @return string
     */
    public static function createUrl(string $appId, string $host = null, string $cluster = null): string
    {
        $query = [
            'protocol' => 7,
            'client' => 'php',
            'version' => ApiSettings::getVersion(),
            'flash' => "false"
        ];
        if (!isset($host)) {
            $host = ($cluster !== null) ? "ws-{$cluster}.pusher.com" : 'ws.pusherapp.com';
        }

        $endpoint = 'ws://' . $host . '/app/' . $appId . '?' . \http_build_query($query);
        // echo $endpoint, PHP_EOL;

        //! wss:// later
        return $endpoint;
    }
}
