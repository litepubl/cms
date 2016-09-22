<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

use Page\Plugin;
use shop\YandexMarket;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test install and uninstall yandex market plugin');
$yamarket = new YandexMarket($i, '150yamarket');
//$data = $yamarket->load('shop/yamarket');

$plugin = new Plugin($i, '150yamarket');
$plugin->install('yandexmarket', 160);
$plugin->uninstall('yandexmarket');
$plugin->install('yandexmarket', 160);

$i->openPage($yamarket->urlOptions);
$yamarket->screenshot('options');
$yamarket->submit();

$i->wantTo('Test tab in product editor');
$yamarket->open();
$yamarket->fill();
$i->click($yamarket->yandexTab);
usleep(300000);
$yamarket->screenshot('tab');
$i->fillField($yamarket->bid, '20');
$i->fillField($yamarket->cbid, '20');
$yamarket->submit();

$i->wantTo('check xml document');
$url = $i->getAbsoluteUrl();
$a = parse_url($url);
$feedUrl = $a['scheme'] . '://' . $a['host'] . $yamarket->feedUrl;
$xml = file_get_contents($feedUrl);
$i->checkErrorInSource($xml);