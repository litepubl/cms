<?php
namespace Step\Acceptance;

use litepubl\test\init;
use litepubl\utils\Filer;

class Install extends \AcceptanceTester
{

    public function install()
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
require_once(init::$homedir . '/lib/utils/Filer.php');
Filer::delete(init::$homedir . '/storage/data', true, false);
$this->dontSeeFileExists(init::$home . '/storage/data/storage.php');
    }


}