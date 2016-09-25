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
    public $url = '/admin/shop/tickets/';
    public $urlOptions = '/admin/shop/tickets/options/';
    public $urlCats = '/admin/shop/tickets/cats/';
public $catTitle = '#text-cattitle';
    public $cabinetUrl = '/admin/cabinet/tickets/';
    public $addUrl = '/admin/cabinet/tickets/?action=add';
public $title = '#text-title';
public $cat = '#combo-cat';
public $text = '#editor-raw';
public $addButton = 'button[name="newticket"]';
public $message = '#editor-message';
public $send = 'button[name="sendmesg"]';
}
