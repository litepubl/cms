<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\tests\shop;

class S01InstallCest extends \Page\Base
{
    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test install and uninstall shop plugin');
        $this->installPlugin('jslogger', 160);

        if ($i->executeJs('return $("input[name=base]").prop("checked");')) {
            codecept_debug('Skip shop install');
            return;

        }

        $this->reInstallPlugin('base', 160);
        $this->installPlugin('real', 160);

    }
}
