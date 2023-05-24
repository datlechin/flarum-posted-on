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

    private function getOperatingSystem()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $osPlatform = null;

        $osArray = [
            '/windows nt 11/i' => 'Windows 11',
            '/windows nt 10/i' => 'Windows 10',
            '/windows/i' => 'Windows',
            '/mac/i' => 'Mac OS',
            '/ubuntu/i' => 'Ubuntu',
            '/linux/i' => 'Linux',
            '/iphone/i' => 'iPhone',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile',
            '/pixel 5/i' => 'Pixel 5',
            '/pixel 6/i' => 'Pixel 6',
            '/pixel 7/i' => 'Pixel 7',
            '/manjaro/i' => 'Manjaro',
        ];

        foreach ($osArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $osPlatform = $value;
            }
        }

        return $osPlatform;
    }
}
