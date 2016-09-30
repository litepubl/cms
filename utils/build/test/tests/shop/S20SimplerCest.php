<?php
namespace litepubl\tests\shop;

use test\Config;

class S20SimplerCest extends \shop\Simpler
{

    protected function test(\AcceptanceTester $i)
    {
$i->wantTo('Test simpler product editor');
$lang = config::getLang();
$data = $this->load('shop/simpler');

$i->wantTo('Open new simpler editor');
$this->open();
$this->screenShot('new');
$this->uploadImage();
$i->checkError();

$i->wantTo('Fill title and content');
$this->fillTitleContent($data->title, $data->content);
$this->setPrice(1000);
$this->screenShot('title');

$i->wantTo('Select category');
//$i->checkOption($this->category);
$this->screenShot('category');

$this->submit();
$this->screenShot('saved');
}}
