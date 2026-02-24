<?php

namespace Datlechin\PostedOn\Listeners;

use Flarum\Post\Event\Saving;
use Laminas\Diactoros\ServerRequestFactory;

class SavePostedOnToPost
{
    public function handle(Saving $event)
    {
        if (isset($event->data['attributes']['content'])) {
            $event->post->posted_on = $this->getOperatingSystem();
        }
    }

    private function getOperatingSystem(): ?string
    {
        $request = ServerRequestFactory::fromGlobals();
        $userAgent = $request->getHeaderLine('User-Agent');

        if (preg_match('/iPhone OS (\d+[._]\d+)/i', $userAgent, $m)) {
            return 'iOS ' . str_replace('_', '.', $m[1]);
        }
        if (preg_match('/iPad.*OS (\d+[._]\d+)/i', $userAgent, $m)) {
            return 'iPadOS ' . str_replace('_', '.', $m[1]);
        }
        if (preg_match('/Mac OS X (\d+[._]\d+(?:[._]\d+)?)/i', $userAgent, $m)) {
            return 'macOS ' . str_replace('_', '.', $m[1]);
        }
        if (preg_match('/Windows NT 10\.0.*Build\/(\d{5,})/i', $userAgent, $m)) {
            return ((int) $m[1] >= 22000) ? 'Windows 11' : 'Windows 10';
        }
        if (preg_match('/Windows NT 10/i', $userAgent)) {
            return 'Windows 10';
        }
        if (preg_match('/Windows NT 6\.3/i', $userAgent)) {
            return 'Windows 8.1';
        }
        if (preg_match('/Windows NT 6\.\d/i', $userAgent)) {
            return 'Windows';
        }
        if (preg_match('/Android (\d+(\.\d+)?)/i', $userAgent, $m)) {
            return 'Android ' . $m[1];
        }
        if (preg_match('/Ubuntu/i', $userAgent)) {
            return 'Ubuntu';
        }
        if (preg_match('/Manjaro/i', $userAgent)) {
            return 'Manjaro';
        }
        if (preg_match('/Linux/i', $userAgent)) {
            return 'Linux';
        }
        if (preg_match('/BlackBerry/i', $userAgent)) {
            return 'BlackBerry';
        }

        return null;
    }
}
