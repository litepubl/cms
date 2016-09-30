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

class BuyPage extends \Page\Base
{
    protected $url = '/admin/shop/';
protected $productLink = '.product-link';
protected $buyButton = 'button[value=buybutton]';
protected $cashButton = 'button[value=cash]';
protected $editAddrButton = 'button[name=editaddr]';
protected $noteEditor = 'textarea[name^=note]';
protected $count = 'input[type=text][name^=count]';
protected $continueButton = 'button[name=continue]';
protected $backButton = 'button[name=back]';
protected $detailsButton = 'button[type=submit]';

protected function getStep(): string
{
return $this->tester->executeJs('return $("input[name=step]").val();');
}

protected function isAddrEdit(): bool
{
return 'address' == $this->getStep();
}

protected function fillAddress(\StdClass $data)
{
foreach (get_object_vars($data) as $k => $v) {
$this->tester->executeJs("\$('[name=$k]').val('$v');");
}
}

}
