<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

use shop\Editor;
use test\config;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test product editor');
$editor = new Editor($i, '121editor');
$lang = config::getLang();
$data = $editor->load('shop/editor');
check

$i->wantTo('Open new editor');
$editor->open();
$editor->screenShot('new');
$editor->uploadImage();
$i->checkError();

$i->wantTo('Fill title and content');
$editor->fillTitleContent($data->title, $data->content);
$editor->setPrice(2000);

$editor->screenShot('title');



$i->wantTo('Select category');
$i->click($data->catalog);
$i->checkOption($data->hits);
$editor->screenShot('category');

$editor->submit();
$editor->screenShot('saved');
