<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace Page;

class Service extends Base
{
    public $url = '/admin/service/';
    public $hostText = '#text-host';
    public $hostFixture = 'shop.cms';
    public $loginText = '#text-login';
    public $loginFixture = 'super';
    public $passwordText = '#password-password';
    public $passwordFixture = 'super';
    public $autoButton = '#submitbutton-autoupdate';

    public $runUrl = '/admin/service/run/';
    public $runText = '#editor-content';
    public $runFixture = '$o = \litepubl\core\litepubl::$app->options;
$o->version -= 0.02;';
}
