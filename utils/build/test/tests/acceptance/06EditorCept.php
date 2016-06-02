<?php 

use Page\Editor;
use test\config;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test post editor');
$editor = new Editor($i);
$lang = config::getLang();
$data = $editor->load('editor');

$editor->open();
$i->screenShot('06.01empty');
$editor->upload('img1.jpg');
$i->checkError();

$editor->fillTitleContent($data->title, $data->content);
$i->screenShot('06.02title');

$i->wantTo('Select category');
$i->checkOption($editor->category);
$i->screenShot('06.03cat');

$i->wantTo('test date time tab');
$i->click($lang->posted);
$i->checkError();
$i->screenShot('06.04datetime');
$i->see($lang->date);
$i->click($editor->calendar);
$i->see($lang->calendar);

//$i->waitForJS('return litepubl.tabs.flagLoaded');
sleep(1);
$i->screenShot('06.05calendar');
$i->click(['link' => '2']);
$i->click($data->close);

$i->screenShot('06.06final');
//final submit
$i->executeJs('$("form:last").submit();');
$i->checkError();
$i->screenShot('06.07saved');
