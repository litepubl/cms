<?php

namespace Page;
use litepubl\test\config;

class Install
{
use TesterTrait;

    // include url of current page
    public static $url = '/';
public static $langForm = '#langform';
public static $langCombo = '#combo-lang';
public static $langSubmit= '#langform [name=changelang]';

public static $form = '#form';
      public static $email = '#text-email';
      public static $name = '#text-name';
      public static $description = '#text-description';
      public static $dbname = '#text-dbname';
      public static $dblogin= '#text-dblogin';
      public static $dbpassword = '#text-dbpassword';
      public static $dbprefix = '#text-dbprefix';

public function changeLanguage($name)
{
$i = $this->tester;
$i->selectOption(static::langcombo, $name);
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

}