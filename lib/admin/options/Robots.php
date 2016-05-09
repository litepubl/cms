<?php

namespace litepubl\admin\options;
use litepubl\pages\RobotsTxt;
use litepubl\pages\Appcache;
use litepubl\view\Lang;

class Robots extends \litepubl\admin\Menu
{

    public function getContent() {
        $admin = $this->admintheme;
$lang = Lang::admin('options');
$args = $this->newArgs();

                $args->formtitle = 'robots.txt';
                $args->robots = RobotsTxt::i()->text;
                $args->appcache = Appcache::i()->text;
                $tabs = $this->newTabs($this->admintheme);
                $tabs->add('robots.txt', '[editor=robots]');
                $tabs->add('manifest.appcache', '[editor=appcache]');
                return $admin->form($tabs->get() , $args);
}

    public function processForm() {
                $robo = RobotsTxt::i();
                $robo->text = $_POST['robots'];
                $robo->save();

                $appcache  = Appcache::i();
                $appcache ->text = $_POST['appcache'];
                $appcache ->save();
}

}
