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
$service = new Service($i, 30update');
$service->open($service-.runUrl);
$i->fillField($service->title, $cats->titleFixture);

$i->screenShot('06.02title');

//final submit
$i->executeJs('$("form:last").submit();');
$i->checkError();
$i->screenShot('07.07saved');
