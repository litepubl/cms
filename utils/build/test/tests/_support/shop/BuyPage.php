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
public $cashButton = 'button[value=cash]';
public $editAddrButton = 'button[name=editaddr]';
public $noteEditor = 'textarea[name^=note]';
public $continueButton = 'button[name=continue]';
public $detailsButton = 'button[type=submit]';

public function getStep(): string
{
return $this->tester->executeJs('return $("input[name=step]").val();');
}

public function isAddrEdit(): bool
{
return 'address' == $this->getStep();
}

public function fillAddress(\StdClass $data)
{
foreach (get_object_vars($data) as $k => $v) {
$this->tester->executeJs("\$('[name=$k]').val('$v');");
}
}

}
