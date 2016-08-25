<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

use Page\Install;
use Page\Installed;
use test\config;

if (config::exists('admin')) {
    codecept_debug('Install skiped');
    return;
}

$i = new AcceptanceTester($scenario);
$install = new Install($i);
$install->removeData();
$i->wantTo('Open install form');
$i->openPage($install->url);
$i->screenShot('01.01form');

//$install->switchLanguages();
$i->wantTo('Fill install form');
$data = config::load('install');
$i->fillField($install->email, $data->email);
$i->fillField($install->name, $data->name);
$i->fillField($install->description, $data->description);
$i->fillField($install->dbname, $data->dbname);
$i->fillField($install->dblogin, $data->dblogin);
$i->fillField($install->dbpassword, $data->dbpassword);
$i->fillField($install->dbprefix, $data->dbprefix);
$i->screenshot('01.02filled');

$i->click($install->submit);
$i->checkError();
$i->assertFileExists(config::$home . '/storage/data/storage.inc.php', 'CMS not installed: storage not found');

$installed = new Installed($i);
$installed->saveAccount();
$i->screenShot('01.03installed');

$i->wantTo('Open login page');
$i->click($installed->link);
$i->checkError();
