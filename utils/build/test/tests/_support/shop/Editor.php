<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace shop;

use test\config;
class Editor extends CommonEditor
{
public $lang;
public $data;

    public $url = '/admin/shop/products/editor/';
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

    public function __construct(\AcceptanceTester $I, string $screenshotName = '')
{
parent::__construct($I, $screenshotName);
$this->lang = config::getLang();
$this->data = $this->load('shop/editor');
}

public function fill()
{
$i = $this->tester;
$this->uploadImage();
$i->checkError();

$i->wantTo('Fill title and content');
$this->fillTitleContent($this->data->title, $this->data->content);
$this->setPrice($this->data->price);
$i->fillField($this->sale_price, $this->data->sale_price);
$i->fillField($this->saleFrom, date('d.m.Y'));
$i->fillField($this->saleTo, date('d.m.Y', strtotime($this->data->saleTo)));

$i->fillField($this->saleFromTime, '00:0000');
$i->fillField($this->saleToTime, '00:0000');
}

}
