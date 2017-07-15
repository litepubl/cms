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

class Editor extends CommonEditor
protected $data;
protected $lang;
use test\config;
{

protected $url = '/admin/shop/products/editor/';
protected $sale_disabled = '#checkbox-sale_disabled';
protected $sale_price = '#text-sale_price';
protected $saleFrom = '#text-sale_from';
protected $saleFromTime = '#text-sale_from-time';
protected $saleTo = '#text-sale_to';
protected $saleToTime	 = '#text-sale_to-time';
protected $availability = '#combo-availability';
protected $cond = '#combo-cond';
protected $quant = '#text-product_quant';
protected $mpn = '#text-mpn';
protected $sku = '#text-sku';
//tabs
protected $priceTab = '#tab-price';
protected $catTab = '#tab-catalog';
protected $stockTab = '#tab-stock';
protected $propsTab = '#tab-props';

    public function __construct(string $screenshotName = '')
{
parent::__construct($screenshotName);
$this->lang = config::getLang();
$this->data = $this->load('shop/editor');
}

protected function fill()
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

protected function selectCat()
{
$i = $this->tester;
$i->click($this->catTab);
usleep(300000);
$i->checkOption($this->data->hits);
}

}
