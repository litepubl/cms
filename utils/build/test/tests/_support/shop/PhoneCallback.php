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

class PhoneCallback extends \Page\Base
{
    public $link = '#phone-callback-link';
public $name = '#text-contactname';
public $phone = '#text-phone';
    public $skype = '#skype-link';
public $ok = 'button[data-index="0"]';
public $closeButton = 'button.close';
public $compas = '.street-address';
}