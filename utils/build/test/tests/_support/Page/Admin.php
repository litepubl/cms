<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace Page;

use test\config;

class Admin extends Base
{
    public $url = '/admin/';
    private $js;

    public function getMenu()
    {
        $i = $this->tester;
        $i->wantTo('Get menu links');

        if (!$this->js) {
            $this->js = file_get_contents(__DIR__ . '/js/adminLinks.js');
        }

        $result = $i->executeJs($this->js);
        //delete logout link
        array_pop($result);
        return $result;
    }

    public function submit()
    {
        $i = $this->tester;
        $i->executeJs('$(function(){$("form:last").submit();});');
        //sleep(2);
        //codecept_debug(date('i:s'));
    }

    public function getLinks($name)
    {
        $s = file_get_contents(config::$_data . "/$name.txt");
        $s = trim(str_replace("\r", '', $s));
        return explode("\n", $s);
    }

    public function getPages()
    {
        return $this->getLinks('adminPages');
    }

    public function getForms()
    {
        return $this->getLinks('adminForms');
    }

    public function getAjax()
    {
        return $this->getLinks('ajaxLinks');
    }
}
