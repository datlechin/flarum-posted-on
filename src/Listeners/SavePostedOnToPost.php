<?php

namespace Datlechin\PostedOn\Listeners;

use Flarum\Post\Event\Saving;

class SavePostedOnToPost
{
    public function handle(Saving $event)
    {
        $post = $event->post;

        $post->posted_on = $this->getOperatingSystem();
    }

    private function getOperatingSystem()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $osPlatform = null;

        $osArray = array(
            '/windows/i' => 'Windows',
            '/mac/i' => 'Mac OS',
            '/linux/i' => 'Linux',
            '/iphone/i' => 'iPhone',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        );

        foreach ($osArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                $osPlatform = $value;
            }
        }

        return $osPlatform;
    }
}
