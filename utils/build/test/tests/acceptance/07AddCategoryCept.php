<?php 

use Page\Cats;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test category editor');
$cats = new Cats($i);
$cats->open();
$i->screenShot('07.01addcats');

$i->fillField($cats->title, $cats->titleFixture);
//$i->selectOption($cats->parent, 
$i->screenShot('06.02title');

//final submit
$i->executeJs('$("form:last").submit();');
$i->checkError();
$i->screenShot('07.07saved');
