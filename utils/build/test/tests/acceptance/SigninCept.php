<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('log in as Admin');
$I->amOnPage('/admin/login/');
$I->fillField('[name=email]','j@jj.jj');
$I->fillField('#password-password','NWjoTT29Fs8xq6Nx6ilnfg');
$I->click('#submitbutton-log_in');
$I->seeCurrentUrlEquals('/admin/');
