<?php 

use Page\Editor;
use test\config;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test post editor');
$editor = new Editor($i);
$lang = config::getLang();
$data = $editor->load('editor');

$editor->open();
//$editor->upload('img1.jpg');
$i->checkError();

$editor->fillTitleContent($data->title, $data->content);

$i->wantTo('test date time tab');
$i->click($lang->posted);
$i->checkError();
$i->see($lang->date);
$i->click($editor->calendar);
$i->see($lang->calendar);
$i->screenShot('06calendar');
$i->click('2');
$i->click($lang->close);

//final submit
$i->executeJs('$("form:last").submit();');
$i->checkError();
$i->screenShot('06editor');
