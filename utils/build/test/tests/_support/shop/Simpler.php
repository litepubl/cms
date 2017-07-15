<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace shop;

class Simpler extends CommonEditor
{
    protected $url = '/admin/shop/products/simpler/';
    protected $cat = '#combo-cat';

    protected function fillProduct(float $price, int $cat)
    {
        $i = $this->tester;
        $i->fillField($this->content, $content);
//$i->selectOption($this->cat, $cat);
$i->executeJs("\$('[value=$cat]', '$this->cat').prop('select', true);");
}
}
