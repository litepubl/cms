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

class Editor extends CommonEditor
{
    const URL = '/admin/shop/products/editor/';
public $sale_disabled = '#checkbox-sale_disabled',
public $sale_price = '#text-sale_price';
public $availability = '#combo-availability';
public $cond = '#combo-cond';
public $quant = '#text-product_quant';
public $mpn = '#text-mpn';
public $sku = '#text-sku';

    public function fillProduct(float $price, int $cat)
    {
        $i = $this->tester;
        $i->fillField($this->price, $price);
}

}

    }
//$i->selectOption($this->cat, $cat);
$i->executeJs("\$('[value=$cat]', '$this->cat').prop('select', true);");
}
