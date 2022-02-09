<?php

namespace Datlechin\PostedOn\Listener;

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
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
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
