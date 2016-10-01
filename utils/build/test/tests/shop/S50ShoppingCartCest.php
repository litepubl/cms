<?php

namespace litepubl\tests\shop;

class S50ShoppingCartCest extends \Page\Base
{
    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test install and uninstall shopping cart');
        $this->reInstallPlugin('shoppingcart', 160);

    }
}
