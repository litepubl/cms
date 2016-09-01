<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace Page;

class Simpler extends Editor 
{
    const URL = '/admin/shop/products/simpler/';
    public $price = '#text-price';
    public $gtin = '#text-gtin';
    public $currency = '#combo-currency';
    public $cat = '#combo-cat';

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
