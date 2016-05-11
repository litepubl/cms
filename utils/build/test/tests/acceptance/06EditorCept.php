<?php 

use Page\Editor;
use test\config;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test post editor');
$editor = new Editor($i);
$lang = config::getLang();
$data = $editor->load('editor');

$editor->open();
$editor->upload('img1.jpg');
$i->checkError();

$editor->fillTitleContent($data->title, $data->content);

$i->wantTo('Select category');
$i->checkOption($editor->category);

$i->wantTo('test date time tab');
$i->click($lang->posted);
$i->checkError();
$i->see($lang->date);
$i->click($editor->calendar);
$i->see($lang->calendar);
$i->screenShot('06calendar');
//$i->waitForJS('return litepubl.tabs.flagLoaded');
sleep(1);

$i->click(['link' => '2']);
$i->click($data->close);

//final submit
$i->executeJs('$("form:last").submit();');
$i->checkError();
$i->screenShot('06editor');
