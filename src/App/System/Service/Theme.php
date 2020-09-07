<?php
namespace Be\App\System\Service;

class Theme extends \Be\System\Service
{

    private $theme = null;
    public function getThemes()
    {
        if ($this->theme === null) {
            $this->theme = array();
        }
        return $this->theme;
    }

    public function getThemeKeyValues(){
        return [];
    }


    public function getThemeCount()
    {
        return 1;
    }

}
