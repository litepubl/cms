<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace shop;

class Board extends \page\Base
{
    public $url = '/admin/shop/';
    public $failUrl = '/admin/cabinet/fail/';
    public $cabinetUrl = '/admin/cabinet/';
public $error = '.text-warning';

public function check()
{
$this->tester->wantTo('Page found');
$this->tester->dontSeeElement($this->error);
}
}