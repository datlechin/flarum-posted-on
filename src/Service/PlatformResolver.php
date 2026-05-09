<?php

/*
 * This file is part of datlechin/flarum-posted-on.
 *
 * Copyright (c) 2026 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\PostedOn\Service;

use Datlechin\PostedOn\ValueObject\Platform;
use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Resolves a Platform value object from a request.
 *
 * Combines two signals:
 *   1. The legacy `User-Agent` header.
 *   2. User-Agent Client Hints (Sec-CH-UA-*) when the browser sends them.
 *
 * Hints are essential for distinguishing Windows 10 from Windows 11 (both
 * report `Windows NT 10.0` in the legacy UA) and for surfacing the real
 * macOS version (Apple froze it at 10.15.7 in the UA).
 */
class PlatformResolver
{
    /**
     * Operating systems whose User-Agent version is frozen for compat. We
     * trust the parsed version only when Client Hints back it up.
     */
    private const VERSION_FROZEN_IN_UA = ['Windows', 'Mac'];

    /**
     * Best-effort family map for OSes the upstream library doesn't expose
     * a family for, used by the frontend to pick an icon.
     *
     * @var array<string, string>
     */
    private const OS_FAMILY_FALLBACK = [
        'Mac' => 'Mac',
        'iOS' => 'iOS',
        'iPadOS' => 'iOS',
        'Windows' => 'Windows',
        'Windows Phone' => 'Windows',
        'Android' => 'Android',
        'Chrome OS' => 'Chrome OS',
    ];

    public function resolve(ServerRequestInterface $request): Platform
    {
        $userAgent = $request->getHeaderLine('User-Agent');
        if ($userAgent === '') {
            return new Platform();
        }

        $hints = ClientHints::factory($this->serverParamsFor($request));
        $hasPlatformVersionHint = trim($request->getHeaderLine('Sec-CH-UA-Platform-Version'), " \"") !== '';

        $detector = new DeviceDetector($userAgent, $hints);
        $detector->parse();

        if ($detector->isBot()) {
            return new Platform(isBot: true);
        }

        $os = $detector->getOs();
        $client = $detector->getClient();

        $osName = $this->str($os['name'] ?? null);
        $rawOsName = $osName;
        $osVersion = $this->str($os['version'] ?? null);
        $osFamily = $this->str($os['family'] ?? null) ?? self::OS_FAMILY_FALLBACK[$osName ?? ''] ?? null;

        if ($osName === 'Mac') {
            $osName = 'macOS';
        }

        // Respect the version freeze: drop the version when we have nothing
        // but the legacy UA to back it up.
        if ($rawOsName !== null && in_array($rawOsName, self::VERSION_FROZEN_IN_UA, true) && ! $hasPlatformVersionHint) {
            $osVersion = null;
        }

        return new Platform(
            osName: $osName,
            osVersion: $osVersion,
            osFamily: $osFamily,
            clientName: $this->str($client['name'] ?? null),
            clientVersion: $this->str($client['version'] ?? null),
            clientType: $this->str($client['type'] ?? null),
            clientFamily: $this->str($client['family'] ?? null) ?? $this->str($client['engine'] ?? null),
            deviceType: $this->str($detector->getDeviceName()) ?: null,
            deviceBrand: $this->str($detector->getBrandName()) ?: null,
            deviceModel: $this->str($detector->getModel()) ?: null,
            isBot: false,
        );
    }

    /**
     * matomo/device-detector reads hints from `$_SERVER`-style HTTP_*
     * keys. Build that view from the request's actual headers so the
     * resolver works the same in tests as it does in production.
     *
     * @return array<string, string>
     */
    private function serverParamsFor(ServerRequestInterface $request): array
    {
        $server = $request->getServerParams();
        foreach ($request->getHeaders() as $name => $values) {
            if (! str_starts_with(strtolower($name), 'sec-ch-ua')) {
                continue;
            }
            $key = 'HTTP_'.str_replace('-', '_', strtoupper($name));
            $server[$key] = implode(', ', $values);
        }
        return $server;
    }

    private function str(mixed $value): ?string
    {
        if ($value === null || $value === '' || $value === DeviceDetector::UNKNOWN) {
            return null;
        }
        return (string) $value;
    }
}
