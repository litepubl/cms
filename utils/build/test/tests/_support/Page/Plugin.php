<?php
namespace Page;

class Plugin extends Base
{
    public  $url = '/admin/plugins/';


public function install(string $name)
{
$this->open();
$i = $this->tester;
$i->wantTo("Install plugin $name");
$i->checkOption("input[name=$name]");
$i->click($this->updateButton);
$i->checkError();
}

public function uninstall(string $name)
{
$this->open();
$i = $this->tester;
$i->wantTo("Uninstall plugin $name");
$i->UncheckOption("input[name=$name]");
$i->click($this->updateButton);
$i->checkError();
}

}