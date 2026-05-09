<?php

/*
 * This file is part of datlechin/flarum-posted-on.
 *
 * Copyright (c) 2022 Ngo Quoc Dat.
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

    new Extend\Locales(__DIR__ . '/locale'),

    (new Extend\Middleware('forum'))
        ->add(AdvertiseClientHints::class),

    (new Extend\Middleware('api'))
        ->add(AdvertiseClientHints::class),

    (new Extend\Event())
        ->listen(PostSaving::class, SavePostedOnToPost::class),

    (new Extend\ApiResource(PostResource::class))
        ->fields(fn () => [
            Schema\Str::make('postedOn')
                ->get(fn (Post $post) => $post->posted_on),
        ]),

    (new Extend\ApiResource(UserResource::class))
        ->fields(fn () => [
            Schema\Boolean::make('disclosePostedOn')
                ->get(fn (User $user) => (bool) $user->disclose_posted_on)
                ->writable()
                ->set(fn (User $user, bool $value) => $user->disclose_posted_on = $value),
        ]),
];
