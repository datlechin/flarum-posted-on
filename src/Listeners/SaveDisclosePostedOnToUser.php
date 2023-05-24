<?php

namespace Datlechin\PostedOn\Listeners;

use Flarum\User\Event\Saving;
use Illuminate\Support\Arr;

class SaveDisclosePostedOnToUser
{
    public function handle(Saving $event)
    {
        $attributes = Arr::get($event->data, 'attributes', []);

        if (isset($attributes['disclosePostedOn'])) {
            $event->user->disclose_posted_on = $attributes['disclosePostedOn'];
        }
    }
}
