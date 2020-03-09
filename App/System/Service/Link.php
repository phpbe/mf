<?php
namespace Be\App\System\Service;

use Be\System\Be;
use Be\System\Service;
use Be\System\Cache;

class Link extends Service
{


    public function updateCache()
    {
        Cache::set('System:Link:links', $this->getLinks());
    }


    public function getLinksWithCache()
    {
        $links = Cache::get('System:Link:links');
        if ($links !== false) return $links;

        $links = $this->getLinks();
        Cache::set('System:Link:links', $links);

        return $links;
    }

    public function getLinks()
    {
        $links = Be::newTable('system_link')
            ->where('block', 0)
            ->orderBy('ordering', 'desc')
            ->getObjects();

        return $links;
    }

}
