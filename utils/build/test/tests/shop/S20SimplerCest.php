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

use test\Config;

class S20SimplerCest extends \shop\Simpler
{

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test simpler product editor');
        $lang = config::getLang();
        $data = $this->load('shop/simpler');

        $i->wantTo('Open new simpler editor');
        $this->open();
        $this->screenShot('new');
        $this->uploadImage();
        $i->checkError();

        $i->wantTo('Fill title and content');
        $this->fillTitleContent($data->title, $data->content);
        $this->setPrice(1000);
        $this->screenShot('title');

        $i->wantTo('Select category');
        //$i->checkOption($this->category);
        $this->screenShot('category');

        $this->submit();
        $this->screenShot('saved');

    }
}
