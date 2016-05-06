<?php 

$i = new AcceptanceTester($scenario);
$i->wantTo('Wheare are');
$url = $i->grabFromCurrentUrl());
codexcept::debug($url);
