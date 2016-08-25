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
$service->open($service->runUrl);
$i->fillField($service->runText, $service->runFixture);
$service->screenShot('runscript');
$service->submit();
return;
$service->open($service->url);
$i->fillField($service->hostText, $service->hostFixture);
$i->fillField($service->loginText, $service->loginFixture);
$i->fillField($service->passwordText, $service->passwordFixture);
$i->click($service->autoButton);
        $i->waitForElement('table', 300);
$i->checkError();
