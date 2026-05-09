<?php

/*
 * This file is part of datlechin/flarum-posted-on.
 *
 * Copyright (c) 2026 Ngo Quoc Dat.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Datlechin\PostedOn;

use Datlechin\PostedOn\Listeners\SavePostedOnToPost;
use Datlechin\PostedOn\Middleware\AdvertiseClientHints;
use Flarum\Api\Resource\PostResource;
use Flarum\Api\Resource\UserResource;
use Flarum\Api\Schema;
use Flarum\Extend;
use Flarum\Post\Event\Saving as PostSaving;
use Flarum\Post\Post;
use Flarum\User\User;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    new Extend\Locales(__DIR__ . '/locale'),

    (new Extend\Model(Post::class))
        ->cast('posted_on_meta', 'array'),

    (new Extend\Middleware('forum'))
        ->add(AdvertiseClientHints::class),

    (new Extend\Middleware('api'))
        ->add(AdvertiseClientHints::class),

    (new Extend\Event())
        ->listen(PostSaving::class, SavePostedOnToPost::class),

    (new Extend\Settings())
        ->default('datlechin-posted-on.display_mode', 'os_only')
        ->default('datlechin-posted-on.skip_guests', false)
        ->serializeToForum('postedOnDisplayMode', 'datlechin-posted-on.display_mode'),

    (new Extend\ApiResource(PostResource::class))
        ->fields(fn () => [
            Schema\Str::make('postedOn')
                ->get(fn (Post $post) => $post->posted_on),
            Schema\Arr::make('postedOnMeta')
                ->get(fn (Post $post) => $post->posted_on_meta),
        ]),

    (new Extend\ApiResource(UserResource::class))
        ->fields(fn () => [
            Schema\Boolean::make('disclosePostedOn')
                ->get(fn (User $user) => (bool) $user->disclose_posted_on)
                ->writable()
                ->set(fn (User $user, bool $value) => $user->disclose_posted_on = $value),
        ]),
];
