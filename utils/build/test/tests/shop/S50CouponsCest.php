<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\tests\shop;

class S50CouponsCest extends \Page\Base
{
    protected $url = '/admin/shop/products/coupons/';
    protected $urlOptions = '/admin/shop/tickets/options/';
    protected $catTitle = '#text-cattitle';
    protected $cabinetUrl = '/admin/cabinet/tickets/';

    protected $value = '#text-coupon';
    protected $title = '#text-title';
    protected $expired = '#text-expired';
    protected $iddata = '#text-iddata';    

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test install and uninstall couponsin shop');
        $this ->reInstallPlugin('coupons', 160);

        $data = $this->load('shop/coupons');
        $ulogin = $this->getUlogin();
        $i->openPage($this->url);
        $i->wantTo('Create new ');
        $coupon = $i->grabValueFrom($this->value);
        $i->fillField($this->title, $data->title);
        $i->fillField($this->expired, date('d.m.Y', strtotime('+1 month')));
        $this->screenshot('create');
        $this->submit();
        $i->wantTo('Edit spec condition');
        $i->fillField($this->iddata, $data->iddata);
        $this->screenshot('iddata');
        $this->submit();

        $i->wantTo('Check table link');
        $i->click(['link' => $coupon]);
        $i->checkError();

    }
}
