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

class BuyPage extends \Page\Base
{
    public $url = '/admin/shop/';
public $productLink = '.product-link';
public $buyButton = 'button[value=buybutton]';

}