<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

use shop\Editor;
use test\config;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test product editor');
$editor = new Editor($i, '121editor');
$lang = config::getLang();
$data = $editor->load('shop/editor');

$i->wantTo('Open new editor');
$editor->open();
$editor->screenShot('new');
$editor->uploadImage();
$i->checkError();

$i->wantTo('Fill title and content');
$editor->fillTitleContent($data->title, $data->content);
$editor->setPrice(2000);
$i->fillField($editor->sale_price, 500);
$i->fillField($editor->saleFrom, date('d.m.Y'));
$i->fillField($editor->saleTo, date('d.m.Y', strtotime('+1 day')));
$i->fillField($editor->saleFromTime, '00:0000');
$i->fillField($editor->saleToTime, '00:0000');
$editor->screenShot('title');

$i->wantTo('Select category');
$i->click($editor->catTab);
usleep(300000);
$i->checkOption($data->hits);
$editor->screenShot('category');

$i->wantTo('Test stock tab');
$i->click($editor->stockTab);
usleep(300000);
$i->fillField($editor->quant, 999999);
$editor->screenShot('stock');

$editor->submit();
$editor->screenShot('saved');
$i->saveHtml('editor');
codecept_debug($i->grabFromCurrentUrl());
