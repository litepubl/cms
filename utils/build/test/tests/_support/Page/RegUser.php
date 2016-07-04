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

class RegUser extends Base
{
    public $url = '/admin/reguser/';
    public $optionsUrl = '/admin/options/secure/';
    public $groupsUrl= '/admin/users/options/';
    public $enabled = 'input[name=usersenabled]';
    public $reguser = 'input[name=reguser]';
    public $cmtCheckbox= 'input[name=idgroup-5]';

    public $email = '[name=email]';
    public $name = '[name=name]';
    public $submit = '#submitbutton-signup';
}
