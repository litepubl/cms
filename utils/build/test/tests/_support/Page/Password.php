<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace Page;

use test\config;
use test\Utils;
use litepubl\utils\Filer;

class Password extends Base
{
    public $url = '/admin/password/';
    public $email = '#form-lostpass [name=email]';
    public $password = '.password';
    public $submit = '#submitbutton-send';

    public function removeLogs()
    {
        Filer::delete(config::$home . '/storage/data/logs/', false, false);
    }

    public function restore(string $email)
    {
        $i = $this->tester;
        $i->wantTo('Send email');
        $i->fillField($this->email, $email);
        $i->click($this->submit);
        $i->checkError();
        $this->confirmEmail();
        return $i->grabTextFrom($this->password);
    }

    public function confirmEmail()
    {
        $i = $this->tester;
        $i->wantTo('Grab url from email');
        $s = Utils::getSingleFile(config::$home . '/storage/data/logs/');
        $i->assertFalse(empty($s), 'Email file not found');
        $url = Utils::getLine($s, '&confirm=');
        $i->assertNotEmpty($url, 'Url not found in email');
        $i->amOnUrl($url);
        $i->checkError();
    }
}
