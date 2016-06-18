<?php

use Page\Home;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test home image');
$home = new home($i, '09home');
$home->open();
$i->wantTo('Remove current image');
$home->clickTab($home->imageTab);
$i->fillField($home->image, '');
$i->fillField($home->smallimage, '');
$home->submit();

$i->wantTo('See empty hme page');
$i->openPage('/');
$home->screenshot('noimage');
$i->wantTo('Upload image');
$home->open();
$home->clickTab($home->imageTab);
$home->uploadImage('img1.jpg');

$home->submit();
$home->screenshot('uploaded');
$i->openPage('/');
$home->screenshot('image');
