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

use test\config;

class CommonEditor extends \Page\Editor 
{
    public $price = '#text-price';
    public $currency = '#combo-currency';
    public $gtin = '#text-gtin';
protected $dirImages = 'D:\OpenServer/unfuddle/shop/doc/demo/flowers/';
protected static $curImageIndex = 0;

    public function __construct(\AcceptanceTester $I, string $screenshotName = '')
    {
parent::__construct($I, $screenshotName);
$this->url = static::URL;
}

    public function setPrice(float $price, string $currency = '')
    {
        $i = $this->tester;
        $i->fillField($this->price, $price);

if ($currency) {
$i->executeJs("\$('[value=$currency]', '$this->currency').prop('select', true);");
}
}

public function getNextImage(): string
{
$list = glob($this->dirImages . '*.jpg');
return $list[static::$curImageIndex++];
}

public function uploadImage()
{
$tmp = 'temp.jpg';
file_put_contents(config::$_data  . $tmp, file_get_contents($this->getNextImage()));
$this->upload($tmp);
}

}
