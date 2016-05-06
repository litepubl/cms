<?php 

use Page\Login;

$i = new AcceptanceTester($scenario);
$page = new Login($i);

$i->wantTo('Wheare are');
$url = $i->grabFromCurrentUrl());
codexcept::debug($url);
