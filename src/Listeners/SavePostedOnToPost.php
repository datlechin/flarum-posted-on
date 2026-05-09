<?php

/*
 * This file is part of datlechin/flarum-posted-on.
 *
 * Copyright (c) 2026 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\PostedOn\Listeners;

use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use Flarum\Post\Event\Saving;
use Laminas\Diactoros\ServerRequestFactory;

/**
 * Resolves the operating system of the poster from the User-Agent string and,
 * when present, User-Agent Client Hints (Sec-CH-UA-*).
 *
 * Why the rewrite: legacy UA-only sniffing can't tell Windows 11 from
 * Windows 10. Both report `Windows NT 10.0` because Microsoft froze the NT
 * version for compatibility. Client Hints carry the actual platform
 * version (Win 11 = "13.0.0" or higher, Win 10 = "10.0.0"), so we delegate
 * to matomo/device-detector which combines both signals and stays current
 * with new browser/OS releases.
 *
 * For Firefox / Safari / Tor we still get only the legacy UA. The library
 * resolves that to "Windows" with no version rather than guessing, which is
 * the honest answer when the data isn't there.
 */
class SavePostedOnToPost
{
    public function handle(Saving $event): void
    {
        if (! isset($event->data['attributes']['content'])) {
            return;
        }

        $rendered = $this->renderPlatform();
        if ($rendered !== null) {
            $event->post->posted_on = $rendered;
        }
    }

    /**
     * Operating systems whose User-Agent version field is frozen for
     * compatibility, so the legacy UA tells us nothing about the actual
     * release. We trust the parsed version only when Client Hints back it
     * up; otherwise we return the bare OS name.
     */
    private const VERSION_FROZEN_IN_UA = ['Windows', 'Mac'];

    private function renderPlatform(): ?string
    {
        $request = ServerRequestFactory::fromGlobals();
        $userAgent = $request->getHeaderLine('User-Agent');
        if ($userAgent === '') {
            return null;
        }

        $hasPlatformVersionHint = trim($request->getHeaderLine('Sec-CH-UA-Platform-Version'), " \"") !== '';

        $detector = new DeviceDetector($userAgent, ClientHints::factory($_SERVER));
        $detector->parse();

        $os = $detector->getOs();
        if (empty($os) || empty($os['name']) || $os['name'] === DeviceDetector::UNKNOWN) {
            return null;
        }

        $rawName = (string) $os['name'];
        $version = (string) ($os['version'] ?? '');

        // device-detector keeps the legacy "Mac" label; surface it as macOS
        // for end users since that's been Apple's branding since 10.12.
        $displayName = $rawName === 'Mac' ? 'macOS' : $rawName;

        if (in_array($rawName, self::VERSION_FROZEN_IN_UA, true) && ! $hasPlatformVersionHint) {
            return $displayName;
        }

        return $version !== '' ? "{$displayName} {$version}" : $displayName;
    }
}
