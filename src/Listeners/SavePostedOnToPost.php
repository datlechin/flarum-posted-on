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

use Datlechin\PostedOn\Service\PlatformResolver;
use Flarum\Post\Event\Saving;
use Flarum\Settings\SettingsRepositoryInterface;
use Laminas\Diactoros\ServerRequestFactory;

/**
 * Captures the resolved platform on every new post.
 *
 * Bots are dropped (no metadata stored). Guests are dropped when the
 * `skip_guests` setting is on; the user's own privacy toggle is enforced
 * at render time on the frontend so existing rows stay intact when a user
 * later flips it.
 *
 * Two columns are written:
 *   - `posted_on_meta` (JSON) — the rich snapshot the frontend uses for
 *     icons, browser/device tooltips, and forward-compat re-rendering.
 *   - `posted_on` (string) — a flat display string kept for older clients
 *     and for callers that want one line without parsing the JSON.
 */
class SavePostedOnToPost
{
    public function __construct(
        protected PlatformResolver $resolver,
        protected SettingsRepositoryInterface $settings,
    ) {
    }

    public function handle(Saving $event): void
    {
        if (! isset($event->data['attributes']['content'])) {
            return;
        }

        if ((bool) $this->settings->get('datlechin-posted-on.skip_guests', false)
            && $event->actor->isGuest()
        ) {
            return;
        }

        $request = ServerRequestFactory::fromGlobals();
        $platform = $this->resolver->resolve($request);

        if ($platform->isBot) {
            return;
        }

        if ($platform->osName === null && $platform->clientName === null) {
            return;
        }

        $event->post->posted_on_meta = $platform->toArray();
        $event->post->posted_on = $platform->display(
            (string) $this->settings->get('datlechin-posted-on.display_mode', 'os_only'),
        );
    }
}
