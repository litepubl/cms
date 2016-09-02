<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

use shop\Simpler;
use test\config;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test simpler product editor');
$editor = new Simpler($i, '120simpler');
$lang = config::getLang();
$data = $editor->load('simpler');

$i->wantTo('Open new simpler editor');
$editor->open();
$editor->screenShot('new');
$editor->uploadImage();
$i->checkError();

$i->wantTo('Fill title and content');
$editor->fillTitleContent($data->title, $data->content);
$editor->screenShot('title');

$i->wantTo('Select category');
$i->checkOption($editor->category);
$editor->screenShot('category');

$i->wantTo('test date time tab');
$editor->clickTab($lang->posted);
$i->checkError();
$editor->screenShot('datetab');
$i->see($lang->date);

$i->wantTo('Open dialog with calendar');
$i->click($editor->calendar);
$editor->waitForOpenDialog();
$i->waitForElement($editor->datePicker);
$editor->screenShot('calendar');
$i->click(['link' => '2']);
$i->click($data->close);
$editor->waitForcloseDialog();
$editor->screenShot('tosave');
$editor->submit();
$editor->screenShot('saved');
