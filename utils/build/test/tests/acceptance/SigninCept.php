<?php 
use \Codeception\Util\Locator;

$lang = new ArrayObject(parse_ini_file(
dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/lib/languages/ru/admin.ini', false),
ArrayObject::ARRAY_AS_PROPS);
$I = new AcceptanceTester($scenario);
$I->wantTo('log in as Admin');
$I->amOnPage('/admin/login/');
$I->dontSee('exception');
$I->fillField('#form-login [name=email]','j@jj.jj');
$I->fillField('#password-password','NWjoTT29Fs8xq6Nx6ilnfg');
$I->click('#submitbutton-log_in');
$I->dontSee('exception');
$I->seeCurrentUrlEquals('/admin/');

$I->wantTo('log out');
//$I->click(Locator::href('http://cms/admin/logout'));
$I->click($lang->logout);
//'a[href$=/admin/logout/]');
$I->dontSee('exception');