<?php 

use Page\Editor;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test post editor');
$editor = new Editor($i);
$i->openPage($editor::$url);
$i->maximizeWindow();
if ($editor::$url != $i->grabFromCurrentUrl()) {
$login = new Login($i);
$login->login();
}

