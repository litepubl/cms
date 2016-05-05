<?php 

use litepubl\test\init;
use Page\Login as LoginPage;
if (!init::$admin->email) {
init::$admin->email = 'j@jj.jj';
init::$admin->password = 'NWjoTT29Fs8xq6Nx6ilnfg';
}

$i = new AcceptanceTester($scenario);
$loginPage = new LoginPage($i);
$loginPage->login(init::$admin->email, init::$admin->password);

$i->seeCurrentUrlEquals('/admin/');

$i->wantTo('log out');
$v = $i->executeJS('return $("a[href$=\'/logout/\']").length');
//file_put_contents(__DIR__ . '/testlog.txt', $v);

$i->seeElementInDOM('a[href$="logout/"]');
$i->maximizeWindow();

$i->click('a[href$="logout/"]');
//$i->seeLink($lang->logout);
//$i->click($lang->logout);
$i->dontSee('exception');