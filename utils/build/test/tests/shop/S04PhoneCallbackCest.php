<?php

namespace litepubl\tests\shop;

class S04PhoneCallbackCest extends \Page\Base
{
    protected $link = '#phone-callback-link';
    protected $name = '#text-contactname';
    protected $phone = '#text-phone';
    protected $skype = '#skype-link';
    protected $ok = 'button[data-index="0"]';
    protected $closeButton = 'button.close';
    protected $compas = '.street-address';

    protected function test(\AcceptanceTester $i)
    {
        $i->openPage('/');
        $i->wantTo('Test phone call back');
        $i->click($this->link);
        sleep(1);
        $this->screenshot('empty');
        $i->fillField($this->name, 'Pupkin');
        $i->fillField($this->phone, '1235678909875654');
        $this->screenshot('dialog');
        $i->click($this->ok);
        sleep(2);
        $i->checkError();
        $this->screenshot('success');
        $i->click($this->ok);
        sleep(1);

        $i->click($this->compas);
        sleep(4);
        $this->screenshot('compas');
        $i->click($this->closeButton);

    }
}
