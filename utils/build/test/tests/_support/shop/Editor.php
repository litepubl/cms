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
public $sale_disabled = '#checkbox-sale_disabled';
public $sale_price = '#text-sale_price';
public $saleFrom = '#text-sale_from';
public $saleFromTime = '#text-sale_from-time';
public $saleTo = '#text-sale_to';
public $saleToTime	 = '#text-sale_to-time';
public $availability = '#combo-availability';
public $cond = '#combo-cond';
public $quant = '#text-product_quant';
public $mpn = '#text-mpn';
public $sku = '#text-sku';
//tabs
public $priceTab = '#tab-price';
public $catTab = '#tab-catalog';
public $stockTab = '#tab-stock';
public $propsTab = '#tab-props';

}
