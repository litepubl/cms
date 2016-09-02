<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace shop;

class Simpler extends CommonEditor
{
    const URL = '/admin/shop/products/simpler/';
    public $cat = '#combo-cat';

    public function fillProduct(float $price, int $cat)
    {
        $i = $this->tester;
        $i->fillField($this->content, $content);
//$i->selectOption($this->cat, $cat);
$i->executeJs("\$('[value=$cat]', '$this->cat').prop('select', true);");
}
}
