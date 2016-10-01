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

class Hosting extends \Page\Base
{
    protected $url = '/admin/shop/hosting/';
    protected $editUrl = '/admin/shop/hosting/edit/';
    protected $tariffsUrl = '/admin/shop/hosting/tariffs/';
    protected $plugins = '/admin/shop/hosting/plugins/';
    protected $serversUrl = '/admin/shop/hosting/servers/';
    protected $regdomainsUrl = '/admin/shop/hosting/regdomains/';
    protected $optionsUrl = '/admin/shop/hosting/options/';
    protected $runUrl = '/admin/shop/hosting/run/';
    protected $updateUrl = '/admin/shop/hosting/update/';
    protected $releaseUrl = '/admin/shop/hosting/release/';

    protected $cabinetUrl = '/admin/cabinet/hosting/';
    protected $buyUrl = '/admin/cabinet/hosting/buy/';
    protected $cabinetPlugins = '/admin/cabinet/hosting/plugins/';
    protected $domainUrl = '/admin/cabinet/hosting/domain/';
    protected $createUrl = '/admin/cabinet/hosting/create/';

protected $cat = '#combo-cat';
protected $text = '#editor-raw';
protected $addButton = 'button[name="newticket"]';
protected $message = '#editor-message';
protected $send = 'button[name="sendmesg"]';

protected function addServer()
{
$i = $this->tester;
$this->open($this->serversUrl);

}
}
