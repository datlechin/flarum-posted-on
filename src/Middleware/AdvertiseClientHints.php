<?php

/*
 * This file is part of datlechin/flarum-posted-on.
 *
 * Copyright (c) 2026 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\PostedOn\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Tells Chromium-based browsers we'd like to receive User-Agent Client Hints
 * on subsequent requests. Without this header browsers don't volunteer the
 * platform version and we can't tell Windows 11 from Windows 10 (both report
 * `Windows NT 10.0` in the legacy User-Agent).
 *
 * `Critical-CH` makes the platform version specifically a "we'll resend
 * immediately" hint, so the very first POST after a fresh page load already
 * carries it. Otherwise the browser would only attach hints starting from
 * the second request, and a user who reloads then immediately posts would
 * get a hint-less submission.
 *
 * Firefox and Safari ignore these headers on purpose. That's fine — the
 * listener falls back to OS-only resolution and writes "Windows" without a
 * version rather than guessing.
 */
class AdvertiseClientHints implements MiddlewareInterface
{
    private const HINTS = 'Sec-CH-UA, Sec-CH-UA-Platform, Sec-CH-UA-Platform-Version, Sec-CH-UA-Mobile, Sec-CH-UA-Model, Sec-CH-UA-Full-Version-List, Sec-CH-UA-Bitness, Sec-CH-UA-Arch';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request)
            ->withHeader('Accept-CH', self::HINTS)
            ->withHeader('Critical-CH', 'Sec-CH-UA-Platform-Version');
    }
}
