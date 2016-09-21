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

class Partner extends \Page\Base
{
    public $url = '/admin/shop/partners/';
    public $outUrl = '/admin/shop/partners/outpays/';
public $selectButton = 'button[name="select"]';
    public $urlOptions = '/admin/shop/partners/options/';
public $tabPays = '#tab-1';
    public $promoUrl = '/admin/shop/partners/promo/';
public $promoEditor = '#editor-promo';
public $demoText = 'pre';
    public $tariffUrl = '/admin/shop/partners/tariffs/';
public $percent = '#text-percent';

    public $regUrl = '/admin/regpartner/';
    public $cabinetUrl = '/admin/cabinet/partner/';
public $promoCabinet = '/admin/cabinet/partner/promo/';
public $payAccount = '?id=payaccount';
}
