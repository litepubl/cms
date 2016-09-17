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

class Coupons extends \Page\Base
{
    public $url = '/admin/shop/products/coupons/';
    public $urlOptions = '/admin/shop/tickets/options/';
public $catTitle = '#text-cattitle';
    public $cabinetUrl = '/admin/cabinet/tickets/';

public $value = '#text-coupon';
public $title = '#text-title';
public $expired = '#text-expired';
public $iddata = '#text-iddata';	

}