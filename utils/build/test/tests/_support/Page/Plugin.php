<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace Page;

class Plugin extends Base
{
    public $url = '/admin/plugins/';


    public function install(string $name, int $timeout = 10)
    {
        $this->open();
        $i = $this->tester;
        $i->wantTo("Install plugin $name");
        $i->waitForElement("input[name=$name]", 10);
        $i->checkOption("input[name=$name]");
        $i->click($this->updateButton);
        $i->checkError();
        $i->waitForElement("input[name=$name]", $timeout);
        $i->seeCheckboxIsChecked("input[name=$name]");
    }

    public function uninstall(string $name)
    {
        $this->open();
        $i = $this->tester;
        $i->wantTo("Uninstall plugin $name");
        $i->waitForElement("input[name=$name]", 10);
        $i->UncheckOption("input[name=$name]");
        $i->click($this->updateButton);
        $i->checkError();
        $i->waitForElement("input[name=$name]", 10);
        $i->dontSeeCheckboxIsChecked("input[name=$name]");
    }
}
