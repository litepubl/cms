<?php
$I = new WebGuy($scenario);
$I->wantToTest('front page of my site');
$I->amOnPage('/');
$I->see('A sample text on my site');
