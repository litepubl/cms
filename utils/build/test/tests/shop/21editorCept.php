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

$i = new AcceptanceTester($scenario);
$i->wantTo('Test product editor');
$editor = new Editor($i, '121editor');
$i->wantTo('Open new editor');
$editor->open();
$editor->screenShot('new');
$editor->fill();
$editor->screenShot('title');

$i->wantTo('Select category');
$editor->selectCat();
$editor->screenShot('category');

$i->wantTo('Test stock tab');
$i->click($editor->stockTab);
usleep(300000);
$i->fillField($editor->quant, 999999);
$editor->screenShot('stock');

$editor->submit();
$editor->screenShot('saved');
//$i->saveHtml('editor');
codecept_debug($i->grabFromCurrentUrl());
