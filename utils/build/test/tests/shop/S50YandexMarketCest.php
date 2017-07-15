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

class S50YandexMarketCest extends \shop\Editor
{
    protected $urlOptions = '/admin/shop/options/yandex/';
    protected $yandexTab = '#tab-yandex';
    protected $bid = '#text-bid';
    protected $cbid =  '#text-cbid';
    protected $feedUrl = '/yml.xml';

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test install and uninstall yandex market plugin');
        $this ->reInstallPlugin('yandexmarket', 160);
        $i->openPage($this->urlOptions);
        $this->screenshot('options');
        $this->submit();

        $i->wantTo('Test tab in product editor');
        $this->open();
        $this->fill();
        $this->selectCat();

        $i->click($this->yandexTab);
        usleep(300000);
        $this->screenshot('tab');
        $i->fillField($this->bid, '20');
        $i->fillField($this->cbid, '20');
        $this->submit();

        $i->wantTo('check xml document');
        $url = $i->getAbsoluteUrl();
        $a = parse_url($url);
        $feedUrl = $a['scheme'] . '://' . $a['host'] . $this->feedUrl;
        $xml = file_get_contents($feedUrl);
        $i->checkErrorInSource($xml);
    }
}
