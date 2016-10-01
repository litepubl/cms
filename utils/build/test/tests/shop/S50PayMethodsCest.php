<?php

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
