<?php
namespace Step\Acceptance;

use test\config;
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
Filer::delete(config::$home . '/storage/data', true, false);
$this->assertFileNotExists(config::$home . '/storage/data/index.htm', 'Data storage not empty');
    }

}