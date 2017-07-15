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

class S21EditorCest extends \shop\Editor
{

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test product editor');
        $this->open();
        $this->screenShot('new');
        $this->fill();
        $this->screenShot('title');

        $i->wantTo('Select category');
        $this->selectCat();
        $this->screenShot('category');

        $i->wantTo('Test stock tab');
        $i->click($this->stockTab);
        usleep(300000);
        $i->fillField($this->quant, 999999);
        $this->screenShot('stock');

        $this->submit();
        $this->screenShot('saved');
        //$i->saveHtml('editor');
        codecept_debug($i->grabFromCurrentUrl());

    }
}
