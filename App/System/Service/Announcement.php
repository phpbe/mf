<?php
namespace App\System\Service;

use Be\System\Be;
use Be\System\Service;
use Be\System\Cache;

class Announcement extends Service
{

    public function updateCache()
    {
        Cache::set('System:Announcement:Announcements', $this->getAnnouncements());
    }


    public function getAnnouncementsWithCache()
    {
        $announcements = Cache::get('System:Announcement:Announcements');
        if ($announcements !== false) return $announcements;

        $announcements = $this->getAnnouncements();
        Cache::set('System:Announcement:Announcements', $announcements);

        return $announcements;
    }


    public function getAnnouncements()
    {
        $links = Be::newTable('system_announcement')
            ->where('block', 0)
            ->orderBy('ordering', 'desc')
            ->getObjects();

        return $links;
    }



}

