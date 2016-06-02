<?php 

use Page\Cats;
use test\config;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test category editor');
$cats = new Cats($i);
$data = $editor->load('editor');
$cats->open();
$i->screenShot('07.01addcats');

$cats->fillTitleContent($data->title, $data->content);
$i->screenShot('06.02title');

$i->wantTo('Select category');
$i->checkOption($cats->category);
$i->screenShot('06.03cat');

$i->wantTo('test date time tab');
$i->click($lang->posted);
$i->checkError();
$i->screenShot('06.04datetime');
$i->see($lang->date);
$i->click($cats->calendar);
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
$i->screenShot('06.07saved
