<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

use Page\Service;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test update');
$service = new Service($i, '30update');
$i->wantTo('Decrement current version');
$service->open($service->runUrl);
$i->fillField($service->runText, $service->runFixture);
$service->screenShot('runscript');
$service->submit();

$i->wantTo('Fill account form');
$service->open($service->url);
$i->fillField($service->hostText, $service->hostFixture);
$i->fillField($service->loginText, $service->loginFixture);
$i->fillField($service->passwordText, $service->passwordFixture);
$service->screenshot('ftpaccount');
$i->click($service->autoButton);
$i->wantTo('Wait finish update');
        $i->waitForElement('table', 300);
// exclude warning from $i->checkError();
$i->wantTo('Check errors');
$text = htmlspecialchars_decode($i->getVisibleText());
foreach (['exception', 'Parse error', 'Fatal error', 'Notice: Undefined'] as $err) {
    if (strpos($text, $err) !== false) {
        $i->assertTrue(false, $err);
    }
}

$service->screenshot('updated');