<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace Page;

use litepubl\utils\Filer;
use test\config;

class Install extends Base
{

    public $url = '/';
    public $langForm = '#langform';
    public $langCombo = '#combo-lang';
    public $langSubmit= '#changelang';
    public $form = '#form';
    public $email = '#text-email';
    public $name = '#text-name';
    public $description = '#text-description';
    public $dbname = '#text-dbname';
    public $dblogin= '#text-dblogin';
    public $dbpassword = '#text-dbpassword';
    public $dbprefix = '#text-dbprefix';
    public $submit = '#submitbutton-createblog';

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
