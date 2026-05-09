<?php

/*
 * This file is part of datlechin/flarum-posted-on.
 *
 * Copyright (c) 2026 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\PostedOn\Tests\Unit\Service;

use Datlechin\PostedOn\Service\PlatformResolver;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PlatformResolverTest extends TestCase
{
    private PlatformResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new PlatformResolver();
    }

    #[Test]
    public function empty_user_agent_yields_empty_platform(): void
    {
        $platform = $this->resolver->resolve(new ServerRequest());

        $this->assertNull($platform->osName);
        $this->assertNull($platform->clientName);
        $this->assertFalse($platform->isBot);
    }

    #[Test]
    public function bot_request_is_flagged_and_carries_no_platform_data(): void
    {
        $platform = $this->resolver->resolve(
            $this->withUa('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'),
        );

        $this->assertTrue($platform->isBot);
        $this->assertNull($platform->osName);
        $this->assertNull($platform->clientName);
    }

    #[Test]
    public function chrome_on_windows_with_client_hints_resolves_to_windows_11(): void
    {
        $request = $this->withUa('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36')
            ->withHeader('Sec-CH-UA-Platform', '"Windows"')
            ->withHeader('Sec-CH-UA-Platform-Version', '"15.0.0"');

        $platform = $this->resolver->resolve($request);

        $this->assertSame('Windows', $platform->osName);
        $this->assertSame('11', $platform->osVersion);
        $this->assertSame('Chrome', $platform->clientName);
    }

    #[Test]
    public function chrome_on_windows_with_client_hints_resolves_to_windows_10(): void
    {
        $request = $this->withUa('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36')
            ->withHeader('Sec-CH-UA-Platform', '"Windows"')
            ->withHeader('Sec-CH-UA-Platform-Version', '"10.0.0"');

        $platform = $this->resolver->resolve($request);

        $this->assertSame('Windows', $platform->osName);
        $this->assertSame('10', $platform->osVersion);
    }

    #[Test]
    public function firefox_without_client_hints_drops_windows_version(): void
    {
        // Firefox doesn't ship UA Client Hints. We must NOT guess "Windows
        // 10" from the legacy UA — both 10 and 11 report Windows NT 10.0.
        $platform = $this->resolver->resolve(
            $this->withUa('Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0'),
        );

        $this->assertSame('Windows', $platform->osName);
        $this->assertNull($platform->osVersion);
        $this->assertSame('Firefox', $platform->clientName);
    }

    #[Test]
    public function safari_on_mac_without_client_hints_drops_mac_version(): void
    {
        // Apple froze the UA at 10_15_7 since macOS 11. Without Client
        // Hints we cannot tell which actual macOS version is in use.
        $platform = $this->resolver->resolve(
            $this->withUa('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15'),
        );

        $this->assertSame('macOS', $platform->osName);
        $this->assertNull($platform->osVersion);
    }

    #[Test]
    public function chrome_on_mac_with_client_hints_keeps_real_version(): void
    {
        $request = $this->withUa('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36')
            ->withHeader('Sec-CH-UA-Platform', '"macOS"')
            ->withHeader('Sec-CH-UA-Platform-Version', '"14.2.0"');

        $platform = $this->resolver->resolve($request);

        $this->assertSame('macOS', $platform->osName);
        $this->assertNotNull($platform->osVersion);
    }

    #[Test]
    public function iphone_user_agent_resolves_with_full_device(): void
    {
        $platform = $this->resolver->resolve(
            $this->withUa('Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'),
        );

        $this->assertSame('iOS', $platform->osName);
        $this->assertSame('17.2', $platform->osVersion);
        $this->assertSame('Mobile Safari', $platform->clientName);
        $this->assertSame('smartphone', $platform->deviceType);
        $this->assertSame('Apple', $platform->deviceBrand);
        $this->assertSame('iPhone', $platform->deviceModel);
    }

    #[Test]
    public function android_user_agent_keeps_version_from_ua(): void
    {
        // Android UA carries the actual version, no Client Hints needed.
        $platform = $this->resolver->resolve(
            $this->withUa('Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Mobile Safari/537.36'),
        );

        $this->assertSame('Android', $platform->osName);
        $this->assertSame('14', $platform->osVersion);
    }

    #[Test]
    public function display_string_in_os_only_mode(): void
    {
        $request = $this->withUa('Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 Safari/604.1');
        $platform = $this->resolver->resolve($request);

        $this->assertSame('iOS 17.2', $platform->display('os_only'));
    }

    #[Test]
    public function display_string_in_os_browser_mode(): void
    {
        $request = $this->withUa('Mozilla/5.0 (iPhone; CPU iPhone OS 17_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1');
        $platform = $this->resolver->resolve($request);

        $this->assertStringContainsString('iOS 17.2', $platform->display('os_browser'));
        $this->assertStringContainsString('Safari', $platform->display('os_browser'));
    }

    #[Test]
    public function display_string_drops_version_when_only_os_is_known(): void
    {
        $platform = $this->resolver->resolve(
            $this->withUa('Mozilla/5.0 (Windows NT 10.0; rv:120.0) Gecko/20100101 Firefox/120.0'),
        );

        // Firefox's UA still gives us "Windows" without a trustworthy
        // version — display string should reflect that.
        $this->assertSame('Windows', $platform->display('os_only'));
    }

    #[Test]
    public function bot_yields_no_display_string(): void
    {
        $platform = $this->resolver->resolve(
            $this->withUa('Mozilla/5.0 (compatible; Googlebot/2.1)'),
        );

        $this->assertNull($platform->display('os_only'));
    }

    private function withUa(string $userAgent): ServerRequest
    {
        return (new ServerRequest())->withHeader('User-Agent', $userAgent);
    }
}
