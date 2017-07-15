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

class S50PayMethodsCest extends \Page\Base
{
    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test install and uninstall yandex market plugin');
        foreach (['qiwi', 'robokassa', 'webmoney', 'yandexmoney'] as $name) {
            $this->reInstallPlugin($name, 160);

        }


    }
}
