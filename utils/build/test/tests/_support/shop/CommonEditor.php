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

use test\config;

class CommonEditor extends \Page\Editor 
{
    protected $price = '#text-price';
    protected $currency = '#combo-currency';
    protected $gtin = '#text-gtin';
protected $dirImages = 'D:\OpenServer/unfuddle/shop/doc/demo/flowers/';
protected static $curImageIndex = 0;

   protected function setPrice(float $price, string $currency = '')
    {
        $i = $this->tester;
        $i->fillField($this->price, $price);

if ($currency) {
$i->executeJs("\$('[value=$currency]', '$this->currency').prop('select', true);");
}
}

protected function getNextImage(): string
{
$list = glob($this->dirImages . '*.jpg');
return $list[static::$curImageIndex++];
}

protected function uploadImage()
{
$tmp = 'temp.jpg';
file_put_contents(config::$_data  . $tmp, file_get_contents($this->getNextImage()));
$this->upload($tmp);
}

}
