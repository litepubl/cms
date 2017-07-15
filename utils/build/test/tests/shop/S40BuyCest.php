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

class S40BuyCest extends \shop\BuyPage
{
    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test purchase');
        $this->logout();
        $ulogin = $this->getUlogin();
        $data = $this->load('shop/buypage');
        $i->openPage('/');
        $i->wantTo('Open first product');
        $i->click($this->productLink);
        $i->waitForJs('return (\'litepubl\' in window) && (\'authdialog\' in window.litepubl);', 7);
        $this->screenshot('product');

        $i->wantTo('Got to buy page');
        $currentUrl = $i->grabFromCurrentUrl();
        $i->click($this->cashButton);
        sleep(2);
        if ($currentUrl == $i->grabFromCurrentUrl()) {
            $ulogin->_click();
            $i->waitForUrlChanged(10);
        }

        $i->waitForElement('body', 10);
        if ($this->exists($this->editAddrButton)) {
            $i->click($this->editAddrButton);
            $i->checkError();
        }

        if ($this->isAddrEdit()) {
            $this->fillAddress($data->addr);
            $this->submit();
            $i->checkError();

        }

        if ($this->exists($this->noteEditor)) {
            //$i->executeJs("\$('$this->noteEditor').val('$data->note');");
            $i->fillField($this->noteEditor, $data->note);

        }

        if ($this->exists($this->continueButton)) {
            $i->click($this->continueButton);
            $i->checkError();
            $i->click($this->backButton);
            $i->checkError();
            $i->click($this->continueButton);
            $i->checkError();

        }

        if ($this->exists($this->cashButton)) {
            $i->click($this->cashButton);
            $i->checkError();

        }

        $i->click($this->detailsButton);
        $i->checkError();

    }
}
