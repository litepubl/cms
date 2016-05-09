<?php

namespace Page;
use test\config;
use litepubl\utils\Filer;

class Install extends Base
{

    public $url = '/';
public $langForm = '#langform';
public $langCombo = '#combo-lang';
public $langSubmit= '#changelang';

public $form = '#form';
      public  $email = '#text-email';
      public  $name = '#text-name';
      public  $description = '#text-description';
      public  $dbname = '#text-dbname';
      public  $dblogin= '#text-dblogin';
      public  $dbpassword = '#text-dbpassword';
      public  $dbprefix = '#text-dbprefix';
      public  $submit = '#submitbutton-createblog';

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
$i->selectOption($this->langCombo, $name);
$i->click($this->langSubmit);
$i->checkError();
}

public function fillForm()
{
$i = $this->tester;
$i->wantTo('Fill install form');

$data = config::load('install');
$i->fillField($this->email, $data->email);
$i->fillField($this->name, $data->name);
$i->fillField($this->description, $data->description);
$i->fillField($this->dbname, $data->dbname);
$i->fillField($this->dblogin, $data->dblogin);
$i->fillField($this->dbpassword, $data->dbpassword);
$i->fillField($this->dbprefix, $data->dbprefix);

$i->click($this->submit);
$i->checkError();
$i->assertFileExists(config::$home . '/storage/data/storage.inc.php', 'CMS not installed: storage not found');

return $this;
}

public function removeData()
{
$i = $this->tester;
$i->wantTo('Remove data files');

Filer::delete(config::$home . '/storage/data', true, false);
Filer::delete(config::$home . '/storage/cache', true, false);
Filer::delete(config::$home . '/files/js', true, false);
Filer::delete(config::$home . '/files/image', true, false);
Filer::delete(config::$home . '/files/imgresize', true, false);

$i->assertFileNotExists(config::$home . '/storage/data/index.htm', 'Data folder not empty');
return $this;
}

}