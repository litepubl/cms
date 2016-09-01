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

class Editor extends \Page\Editor 
{
    const URL = '/admin/shop/products/editor/';
    public $price = '#text-price';
    public $currency = '#combo-currency';
    public $gtin = '#text-gtin';
public $sale_disabled = '#checkbox-sale_disabled',
public $sale_price = '#text-sale_price';
public $availability = '#combo-availability';
public $cond = '#combo-cond';
public $quant = '#text-product_quant';
public $mpn = '#text-mpn';
public $sku = '#text-sku';

    public function __construct(\AcceptanceTester $I, string $screenshotName = '')
    {
parent::__construct($I, $screenshotName);
$this->url = static::URL;
}

    public function fillProduct(float $price, int $cat)
    {
        $i = $this->tester;
        $i->fillField($this->price, $price);
        $i->fillField($this->content, $content);
    }
//$i->selectOption($this->cat, $cat);
$i->executeJs("\$('[value=$cat]', '$this->cat').prop('select', true);");
}
