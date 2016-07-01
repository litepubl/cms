<?php
namespace litepubl\update;
use litepubl\pages\Contacts;

    function update701()
    {
Contacts::i()->externalFunc('update', null);
}