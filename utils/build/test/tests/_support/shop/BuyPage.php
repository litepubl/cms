<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace shop;

class BuyPage extends \Page\Base
{
    public $url = '/admin/shop/';
public $productLink = '.product-link';

public function check(string $name)
{
$this->tester->wantTo("Exists $name page");
$this->tester->dontSeeElement($this->error);
$this->screenshot($name);
}
}
