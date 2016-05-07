<?php
namespace Page;

class Admin
{
use TesterTrait;

    // include url of current page
    public static $url = '/admin/';

public function getLinksFromMenu()
{
$i = $this->tester;
$i->wantTo('Get menu links');
$js = file_get_contents(__DIR__ . '/adminLinks.js');
$result = $i->executeJs($js);
//codecept_debug(var_export($result, true));
return $result;
}

public function submit()
{
$this->tester->executeJs('$("form:first").submit();');
}

}