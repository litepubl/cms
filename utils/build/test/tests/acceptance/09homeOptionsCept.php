<?php 

use Page\Home;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test home image');
$home = new home($i, '09home');
$home->open();
$i->wantTo('Remove current image');
$i->fillField($home->image, '');
$i->fillField($home->smallimage, '');
$home->submit();

$i->wantTo('See empty hme page');
$i->openPage('/');
$home->screenshot('noimage');
$i->wantTo('Upload image');
$home->open();
$home->setimage('img1.jpg');
