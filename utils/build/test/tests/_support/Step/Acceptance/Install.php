<?php
namespace Step\Acceptance;

use litepubl\test\config;
use litepubl\utils\Filer;

class Install extends \AcceptanceTester
{

    public function install()
    {
}

public function switchLanguages()
{
$this->changeLanguage('en');
$this->changeLanguage('ru');
}

    public function changeLanguage()
    {
        $I = $this;
    }

    public function removeData()
    {
$this->wantTo('Remove data files');
require_once(config::$home . '/lib/utils/Filer.php');
Filer::delete(config::$home . '/storage/data', true, false);
$this->dontSeeFileExists(config::$home . '/storage/data/index.htm');
    }

}