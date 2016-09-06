<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

use shop\Simpler;
use test\config;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test simpler product editor');
$editor = new Simpler($i, '120simpler');
$lang = config::getLang();
$data = $editor->load('shop/simpler');

$i->wantTo('Open new simpler editor');
$editor->open();
$editor->screenShot('new');
$editor->uploadImage();
$i->checkError();

$i->wantTo('Fill title and content');
$editor->fillTitleContent($data->title, $data->content);
$editor->setPrice(1000);
$editor->screenShot('title');

$i->wantTo('Select category');
//$i->checkOption($editor->category);
$editor->screenShot('category');

$editor->submit();
$editor->screenShot('saved');
