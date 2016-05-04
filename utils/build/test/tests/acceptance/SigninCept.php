<?php 

use Page\Login as LoginPage;
use \Codeception\Util\Locator;

$lang = new ArrayObject(parse_ini_file(
dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/lib/languages/ru/admin.ini', false),
ArrayObject::ARRAY_AS_PROPS);

$i = new AcceptanceTester($scenario);
$loginPage = new LoginPage($i);
$loginPage->login('j@jj.jj', 'NWjoTT29Fs8xq6Nx6ilnfg');

$i->seeCurrentUrlEquals('/admin/');

$i->wantTo('log out');
$v = $i->executeJS('return $("a[href$=\'/logout/\']").length');
file_put_contents(__DIR__ . '/testlog.txt', $v);

$i->seeElementInDOM('a[href$="logout/"]');
$i->maximizeWindow();

$i->click('a[href$="logout/"]');
//$i->seeLink($lang->logout);
//$i->click($lang->logout);
$i->dontSee('exception');