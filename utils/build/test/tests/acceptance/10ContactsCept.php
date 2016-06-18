<?php

use Page\Contacts;
use test\config;

$i = new AcceptanceTester($scenario);
$i->maximizeWindow();
$contacts = new Contacts($i);
$data = config::load('contacts');

$i->wantTo('Click contacts on home page');
$i->openPage('/');
$i->click($data->title);
$i->screenShot('10contacts');
$i->wantTo('Send form');
$contacts->sendForm($data->email, $data->message);
$i->wantTo('Send form with empty email');
$contacts->sendForm('', $data->message);
$i->wantTo('Send form without message');
$contacts->sendForm($data->email, '');
