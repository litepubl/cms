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
$i->screenShot("20.$name.install");
$i->checkOption("input[name=$name]");
$i->click($this->updateButton);
$i->checkError();
$i->screenShot("20.$name.installed");
}

public function uninstall(string $name)
{
$this->open();
$i = $this->tester;
$i->wantTo("Uninstall plugin $name");
$i->screenShot("20.$name.uninstall");
$i->UncheckOption("input[name=$name]");
$i->click($this->updateButton);
$i->checkError();
$i->screenShot("20.$name.uninstalled");
}

}