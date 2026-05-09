<?php

/*
 * This file is part of datlechin/flarum-posted-on.
 *
 * Copyright (c) 2026 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\PostedOn\ValueObject;

/**
 * Structured snapshot of where a post was authored from. Stored as JSON on
 * the post and rehydrated on read so the frontend can render rich displays
 * (OS + browser + device) without re-parsing the User-Agent each time.
 *
 * The `osVersion` is intentionally optional. Windows and macOS freeze their
 * version in the legacy UA, so when a request arrives without User-Agent
 * Client Hints we set the OS name only and let the frontend show "Windows"
 * rather than guessing "Windows 10".
 */
final class Platform
{
    public function __construct(
        public readonly ?string $osName = null,
        public readonly ?string $osVersion = null,
        public readonly ?string $osFamily = null,
        public readonly ?string $clientName = null,
        public readonly ?string $clientVersion = null,
        public readonly ?string $clientType = null,
        public readonly ?string $clientFamily = null,
        public readonly ?string $deviceType = null,
        public readonly ?string $deviceBrand = null,
        public readonly ?string $deviceModel = null,
        public readonly bool $isBot = false,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'os' => $this->osName !== null ? [
                'name' => $this->osName,
                'version' => $this->osVersion,
                'family' => $this->osFamily,
            ] : null,
            'client' => $this->clientName !== null ? [
                'name' => $this->clientName,
                'version' => $this->clientVersion,
                'type' => $this->clientType,
                'family' => $this->clientFamily,
            ] : null,
            'device' => [
                'type' => $this->deviceType,
                'brand' => $this->deviceBrand,
                'model' => $this->deviceModel,
            ],
            'bot' => $this->isBot,
        ];
    }

    /**
     * Single-line display string used as the legacy `posted_on` column for
     * back-compat. Format follows the admin's `display_mode` choice.
     */
    public function display(string $mode): ?string
    {
        if ($this->isBot || $this->osName === null) {
            return null;
        }

        $os = $this->osVersion !== null
            ? sprintf('%s %s', $this->osName, $this->osVersion)
            : $this->osName;

        if ($mode === 'os_browser' && $this->clientName !== null) {
            $client = $this->clientVersion !== null
                ? sprintf('%s %s', $this->clientName, $this->clientVersion)
                : $this->clientName;
            return $os.' · '.$client;
        }

        return $os;
    }
}
