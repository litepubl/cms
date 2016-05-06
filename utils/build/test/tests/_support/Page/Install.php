<?php

namespace Page;
use litepubl\test\config;
use litepubl\utils\Filer;

class Install
{
use TesterTrait;

    // include url of current page
    public static $url = '/';
public static $langForm = '#langform';
public static $langCombo = '#combo-lang';
public static $langSubmit= '#changelang';

public static $form = '#form';
      public static $email = '#text-email';
      public static $name = '#text-name';
      public static $description = '#text-description';
      public static $dbname = '#text-dbname';
      public static $dblogin= '#text-dblogin';
      public static $dbpassword = '#text-dbpassword';
      public static $dbprefix = '#text-dbprefix';
      public static $submit = '#submitbutton-createblog';

public function switchLanguages()
{
$this->tester->wantTo('Switch languages');
$this->changeLanguage('English');
$this->changeLanguage('Russian');
}

public function changeLanguage($name)
{
$i = $this->tester;
$i->wantTo('Switch language');
$i->selectOption(static::$langCombo, $name);
$i->click(static::$langSubmit);
$i->checkError();
}

public function fillForm()
{
$i = $this->tester;
$i->wantTo('Fill install form');

$data = config::load('install');

$i->fillField(static::$email, $data->email);
$i->fillField(static::$name, $data->name);
$i->fillField(static::$description, $data->description);
$i->fillField(static::$dbname, $data->dbname);
$i->fillField(static::$dblogin, $data->dblogin);
$i->fillField(static::$dbpassword, $data->dbpassword);
$i->fillField(static::$dbprefix, $data->dbprefix);

$i->click(static::$submit);
$i->checkError();

return $this;
}

public function removeData()
{
$i = $this->tester;
$i->wantTo('Remove data files');
require_once(config::$home . '/lib/utils/Filer.php');
Filer::delete(config::$home . '/storage/data', true, false);
//Filer::append(config::$home . '/storage/log.txt', "deleted\n");
$i->dontSeeFileExists(config::$home . '/storage/data/index.htm');

return $this;
}

}