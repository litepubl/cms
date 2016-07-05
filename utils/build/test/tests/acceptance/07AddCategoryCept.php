<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

use Page\Cats;

$i = new AcceptanceTester($scenario);
$i->wantTo('Test category editor');
$cats = new Cats($i);
$cats->open();
$i->screenShot('07.01addcats');

$i->fillField($cats->title, $cats->titleFixture);
//$i->selectOption($cats->parent, 
$i->screenShot('06.02title');

//final submit
$i->executeJs('$("form:last").submit();');
$i->checkError();
$i->screenShot('07.07saved');
