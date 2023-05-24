<?php

namespace Datlechin\PostedOn\Listeners;

use Flarum\Post\Event\Saving;

class SavePostedOnToPost
{
    public function handle(Saving $event)
    {
        $attributes = $event->data['attributes'];

        if (isset($attributes['content'])) {
            $event->post->posted_on = $this->getOperatingSystem();
        }
    }

    protected function getOperatingSystem()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $osPlatform = null;

        $osArray = [
            '/windows/i' => 'Windows',
            '/mac/i' => 'Mac OS',
            '/ubuntu/i' => 'Ubuntu',
            '/linux/i' => 'Linux',
            '/iphone/i' => 'iPhone',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile',
        ];

        foreach ($osArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $osPlatform = $value;
            }
        }

        return $osPlatform;
    }
}
