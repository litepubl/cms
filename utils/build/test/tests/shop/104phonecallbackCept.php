<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

use shop\PhoneCallback;

$i = new AcceptanceTester($scenario);
$i->openPage('/');
$i->wantTo('Test phone call back');
$widget = new PhoneCallback($i, '104phonecallback');
$i->click($widget->link);
sleep(1);
$widget->screenshot('empty');
$i->fillField($widget->name, 'Pupkin');
$i->fillField($widget->phone, '1235678909875654');
$widget->screenshot('dialog');
$i->click($widget->ok);
sleep(2);
$i->checkError();
$widget->screenshot('success');
$i->click($widget->ok);
sleep(1);

$i->click($widget->compas);
sleep(4);
$widget->screenshot('compas');
$i->click($widget->closeButton);
